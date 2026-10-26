<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Builds the receipt array used by the API receipt, the PDF and the Paytm result page,
 * so journal / author / email / submission date are filled the same way everywhere.
 *
 * It does not assume your relation names: it tries the common ones, then looks the
 * records up by their *_id columns, then scans the article's own columns.
 */
class ReceiptData
{
    /**
     * @param  Model       $payment  the payment model that has toReceipt()
     * @param  object|null $user     logged-in user (API only; the Paytm callback is public)
     */
    public static function build($payment, $user = null): array
    {
        $receipt = $payment->toReceipt();

        try {
            $payment->loadMissing('article');
        } catch (\Throwable $e) {
            // article relation already loaded or named differently: ignore
        }

        $article = $payment->article ?? null;
        $owner   = self::owner($payment, $article);

        $receipt['journal_name'] = $receipt['journal_name'] ?? self::journal($article);

        $receipt['author_name'] = $receipt['author_name']
            ?? self::personName($owner)
            ?? self::attr($article, 'author_name')
            ?? self::personName($user);

        $receipt['author_email'] = $receipt['author_email']
            ?? self::attr($owner, 'email')
            ?? self::attr($article, 'author_email')
            ?? self::attr($article, 'email')
            ?? self::attr($user, 'email');

        $receipt['submitted_at'] = $receipt['submitted_at'] ?? self::submittedAt($payment, $article);

        // Temporary help: if something is still empty, log what the models actually contain.
        // Check storage/logs/laravel.log, then delete this block once everything shows.
        $missing = array_keys(array_filter([
            'journal_name' => $receipt['journal_name'] ?? null,
            'author_name'  => $receipt['author_name'] ?? null,
            'author_email' => $receipt['author_email'] ?? null,
            'submitted_at' => $receipt['submitted_at'] ?? null,
        ], fn ($v) => $v === null || $v === ''));

        if ($missing) {
            Log::warning('Receipt: fields not found', [
                'missing'        => $missing,
                'article_columns' => $article ? array_keys($article->getAttributes()) : null,
                'article_loaded_relations' => $article ? array_keys($article->getRelations()) : null,
                'payment_columns' => array_keys($payment->getAttributes()),
                'owner_columns'   => $owner ? array_keys($owner->getAttributes()) : null,
            ]);
        }

        return $receipt;
    }

    /** The author: article's user relation, the payment's user, or a lookup by *_id. */
    private static function owner($payment, $article): ?Model
    {
        foreach ([[$article, ['user', 'author', 'submittedBy', 'creator', 'owner']], [$payment, ['user', 'author']]] as [$model, $names]) {
            foreach ($names as $name) {
                try {
                    $m = $model?->{$name};
                } catch (\Throwable $e) {
                    $m = null;
                }
                if ($m instanceof Model) {
                    return $m;
                }
            }
        }

        $userClass = '\\App\\Models\\User';
        if (class_exists($userClass)) {
            foreach ([
                self::attr($article, 'user_id'), self::attr($article, 'author_id'), self::attr($article, 'created_by'),
                self::attr($payment, 'user_id'),
            ] as $id) {
                if ($id && ($m = $userClass::find($id))) {
                    return $m;
                }
            }
        }

        return null;
    }

    private static function journal($article): ?string
    {
        if (! $article) {
            return null;
        }

        // relation or plain column called "journal"
        try {
            $j = $article->journal;
        } catch (\Throwable $e) {
            $j = null;
        }
        if ($j instanceof Model) {
            return self::firstFilled($j, ['name', 'title', 'journal_name', 'journal_title']);
        }
        if (is_string($j) && $j !== '' && ! is_numeric($j)) {
            return $j;
        }

        // plain columns
        if ($v = self::firstFilled($article, ['journal_name', 'journal_title'])) {
            return $v;
        }

        // lookup by journal_id
        $id = self::attr($article, 'journal_id');
        if ($id) {
            foreach (['\\App\\Models\\Journal', '\\App\\Models\\Journals'] as $cls) {
                if (class_exists($cls) && ($m = $cls::find($id))) {
                    if ($v = self::firstFilled($m, ['name', 'title', 'journal_name', 'journal_title'])) {
                        return $v;
                    }
                }
            }
        }

        // last resort: any text column with "journal" in its name
        foreach ($article->getAttributes() as $key => $value) {
            if (stripos($key, 'journal') !== false && is_string($value) && $value !== ''
                && ! is_numeric($value) && ! preg_match('/^[0-9a-f-]{32,36}$/i', $value)) {
                return $value;
            }
        }

        return null;
    }

    private static function submittedAt($payment, $article): ?string
    {
        $date = self::attr($article, 'submitted_at')
            ?? self::attr($article, 'submission_date')
            ?? self::attr($article, 'created_at')
            ?? self::attr($payment, 'created_at');

        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        return $date !== null ? (string) $date : null;
    }

    private static function personName($m): ?string
    {
        if (! $m) {
            return null;
        }

        $name = self::firstFilled($m, ['full_name', 'name', 'author_name']);
        if ($name) {
            return $name;
        }

        $joined = trim((string) self::attr($m, 'first_name') . ' ' . (string) self::attr($m, 'last_name'));

        return $joined !== '' ? $joined : null;
    }

    private static function firstFilled($model, array $keys): ?string
    {
        foreach ($keys as $key) {
            $v = self::attr($model, $key);
            if (is_string($v) && trim($v) !== '') {
                return $v;
            }
        }

        return null;
    }

    /** Safe attribute read: missing column or odd model never throws. */
    private static function attr($model, string $key)
    {
        if (! $model) {
            return null;
        }
        try {
            return $model->{$key};
        } catch (\Throwable $e) {
            return null;
        }
    }
}