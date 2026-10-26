<?php

namespace App\Services;

use App\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Talks to Paytm only: signing, HTTP calls, checksum verification.
 * No article/business logic here.
 */
class PaytmGatewayService
{
    private function cfg(string $key): ?string
    {
        if (in_array($key, ['env', 'result_url'], true)) {
            return config("paytm.$key");
        }

        $env = config('paytm.env', 'staging');

        return config("paytm.$env.$key");
    }

    public function host(): string
    {
        return rtrim((string) $this->cfg('host'), '/');
    }

    public function mid(): string
    {
        return (string) $this->cfg('mid');
    }

    /** Paytm page the browser form posts to. */
    public function paymentPageUrl(): string
    {
        return $this->host() . '/theia/processTransaction';
    }

    public function assertConfigured(): void
    {
        foreach (['mid', 'key', 'website'] as $k) {
            if (!$this->cfg($k)) {
                Log::critical("Paytm config '{$k}' is missing");
                throw new PaymentException('Payment gateway is not configured.', 500);
            }
        }
    }

    private function checksumClass(): string
    {
        foreach ([\Paytm\PaytmChecksum\PaytmChecksum::class, \paytm\paytmchecksum\PaytmChecksum::class] as $c) {
            if (class_exists($c)) {
                return $c;
            }
        }

        throw new PaymentException('Paytm checksum library is not installed.', 500);
    }

    /** Signed form fields the browser posts to Paytm. */
    public function buildPaymentParams(string $orderId, string $amount, string $custId, string $callbackUrl): array
    {
        $this->assertConfigured();

        $params = [
            'MID'              => $this->mid(),
            'WEBSITE'          => (string) $this->cfg('website'),
            'INDUSTRY_TYPE_ID' => (string) ($this->cfg('industry_type') ?: 'Retail'),
            'CHANNEL_ID'       => (string) ($this->cfg('channel_id') ?: 'WEB'),
            'ORDER_ID'         => $orderId,
            'CUST_ID'          => $custId,
            'TXN_AMOUNT'       => $amount,
            'CALLBACK_URL'     => $callbackUrl,
        ];

        $params['CHECKSUMHASH'] = $this->checksumClass()::generateSignature($params, (string) $this->cfg('key'));

        return $params;
    }

    private function post(string $url, array $body): array
    {
        $class     = $this->checksumClass();
        $json      = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $signature = $class::generateSignature($json, (string) $this->cfg('key'));

        $payload = json_encode([
            'body' => $body,
            'head' => ['signature' => $signature],
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $request = Http::withBody($payload, 'application/json')->acceptJson()->timeout(20);

        if (app()->environment('local')) {
            $request = $request->withoutVerifying();
        }

        $response = $request->post($url);

        if ($response->failed()) {
            Log::error('Paytm API call failed', [
                'url'    => $url,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new PaymentException('Could not reach the payment gateway. Please try again.', 502);
        }

        return $response->json() ?? [];
    }

    /** Server-to-server order status (source of truth). */
    public function orderStatus(string $orderId): array
    {
        $this->assertConfigured();

        $res = $this->post($this->host() . '/v3/order/status', [
            'mid'     => $this->mid(),
            'orderId' => $orderId,
        ]);

        return $res['body'] ?? [];
    }

    /** Verify the CHECKSUMHASH Paytm sent to the callback. */
    public function verifyCallbackChecksum(array $params): bool
    {
        $checksum = $params['CHECKSUMHASH'] ?? null;
        unset($params['CHECKSUMHASH']);

        if (!$checksum) {
            return false;
        }

        return (bool) $this->checksumClass()::verifySignature($params, (string) $this->cfg('key'), $checksum);
    }
}