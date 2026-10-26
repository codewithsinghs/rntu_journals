@extends('layouts.admin')

@section('content')
    <style>
        @page { size: A4; margin: 14mm; }
        .rcpt-page { max-width: 860px; margin: 0 auto; }
        .rcpt-sheet { background: #fff; border: 1px solid #e3e6ea; border-radius: 8px; padding: 26px 30px; }
        .rcpt-actions { margin-top: 16px; display: flex; gap: 8px; }
        @media print {
            .rcpt-actions { display: none !important; }
            .rcpt-sheet { border: 0; padding: 0; }
        }
    </style>

    <style>
        .rcpt { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #1b263b; font-size: 12px; line-height: 1.4; }
        .rcpt * { box-sizing: border-box; }

        .rcpt-title { margin: 0; text-align: center; font-size: 19px; font-weight: bold; color: #1b263b; }
        .rcpt-sub   { margin: 6px 0 0; text-align: center; font-size: 12px; font-weight: bold; color: #6c757d; }
        .rcpt-rule  { border: 0; border-top: 1px solid #d4d8de; margin: 14px 0 18px; }

        /* status banner */
        .rcpt-banner { width: 100%; border-collapse: separate; border-spacing: 0; border: 1px solid #d0d5dc; border-radius: 8px; margin-bottom: 22px; }
        .rcpt-banner td { padding: 12px 14px; vertical-align: middle; }
        .rcpt-b-pill { width: 17%; }
        .rcpt-b-amt  { width: 26%; text-align: center; }
        .rcpt-pill   { display: inline-block; padding: 7px 14px; border-radius: 5px; border: 1px solid; font-size: 11px; font-weight: bold; letter-spacing: .5px; }
        .rcpt-msg-t  { font-size: 12.5px; font-weight: bold; color: #1b263b; margin-bottom: 3px; }
        .rcpt-msg    { font-size: 10.5px; }
        .rcpt-amt-box { display: block; background: #ffffff; border: 1px solid; border-radius: 6px; padding: 7px 8px; }
        .rcpt-amt-l  { font-size: 8px; font-weight: bold; letter-spacing: .6px; color: #1b263b; text-transform: uppercase; }
        .rcpt-amt-v  { font-size: 15px; font-weight: bold; margin-top: 2px; }

        .rcpt-banner.is-paid    { background: #e8f8f0; border-color: #96e1b4; }
        .is-paid .rcpt-pill     { background: #a8ecc9; border-color: #0a5f37; color: #0a5f37; }
        .is-paid .rcpt-msg, .is-paid .rcpt-amt-v { color: #0a5f37; }
        .is-paid .rcpt-amt-box  { border-color: #96e1b4; }

        .rcpt-banner.is-pending { background: #fff6e2; border-color: #f5d78c; }
        .is-pending .rcpt-pill  { background: #fce2a0; border-color: #965a00; color: #965a00; }
        .is-pending .rcpt-msg, .is-pending .rcpt-amt-v { color: #965a00; }
        .is-pending .rcpt-amt-box { border-color: #f5d78c; }

        .rcpt-banner.is-failed  { background: #fdeced; border-color: #f0aaaf; }
        .is-failed .rcpt-pill   { background: #f8c8cc; border-color: #a02330; color: #a02330; }
        .is-failed .rcpt-msg, .is-failed .rcpt-amt-v { color: #a02330; }
        .is-failed .rcpt-amt-box { border-color: #f0aaaf; }

        .rcpt-banner.is-created { background: #f0f2f5; border-color: #d0d5dc; }
        .is-created .rcpt-pill  { background: #dce0e6; border-color: #555555; color: #555555; }
        .is-created .rcpt-msg, .is-created .rcpt-amt-v { color: #555555; }
        .is-created .rcpt-amt-box { border-color: #d0d5dc; }

        /* section tables */
        .rcpt-h { margin: 0 0 8px; font-size: 14.5px; font-weight: bold; color: #1b263b; }
        .rcpt-grid { width: 100%; border-collapse: collapse; margin-bottom: 22px; table-layout: fixed; }
        .rcpt-grid td { border: 1px solid #d4d8de; padding: 8px 10px; font-size: 11px; vertical-align: middle; word-wrap: break-word; }
        .rcpt-grid td.k { background: #f6f7f9; font-weight: bold; width: 20%; color: #1b263b; }
        .rcpt-grid td.v { width: 30%; }
        .rcpt-grid td.b { font-weight: bold; }
        .rcpt-grid .mono { font-family: DejaVu Sans Mono, Consolas, monospace; font-size: 10px; word-break: break-all; }
        .rcpt-ok   { color: #0a5f37; font-weight: bold; }
        .rcpt-warn { color: #965a00; font-weight: bold; }
        .rcpt-bad  { color: #a02330; font-weight: bold; }

        .rcpt-foot { border-top: 1px solid #d4d8de; padding-top: 10px; margin-top: 4px; text-align: center; color: #6c757d; font-size: 10px; }
    </style>

    @php
        $status = $r['status'] ?? 'created';
        $status = in_array($status, ['paid', 'pending', 'failed'], true) ? $status : 'created';

        $labels = ['paid' => 'Successful', 'pending' => 'Pending', 'failed' => 'Failed', 'created' => 'Not completed'];
        $label  = $labels[$status];

        $banners = [
            'paid'    => ['PAID', 'Article Processing Charge Received', 'The article processing charge for this manuscript has been received successfully.', 'Amount paid'],
            'pending' => ['PENDING', 'Payment Is Being Processed', 'Your payment is still being processed. Please check again shortly from your submissions page.', 'Amount'],
            'failed'  => ['FAILED', 'Payment Was Not Successful', 'The payment could not be completed. Please try again.', 'Amount'],
            'created' => ['NOT PAID', 'Payment Not Completed', 'The payment was started but has not been completed.', 'Amount'],
        ];
        $banner = $banners[$status];
        if ($status === 'failed' && !empty($r['failure_reason'])) {
            $banner[2] = $r['failure_reason'];
        }

        $currency = strtoupper($r['currency'] ?? 'INR');
        $amount   = ($currency === 'INR' ? '₹' : $currency . ' ') . number_format((float) ($r['amount'] ?? 0), 2);

        $journal = !empty($r['journal_name']) ? $r['journal_name'] : config('app.name');

        $submitted = '-';
        if (!empty($r['submitted_at'])) {
            try { $submitted = \Illuminate\Support\Carbon::parse($r['submitted_at'])->format('d M Y'); }
            catch (\Throwable $e) { $submitted = (string) $r['submitted_at']; }
        }

        $v = fn ($key) => (isset($r[$key]) && $r[$key] !== '') ? $r[$key] : '-';

        $statusClass = ['paid' => 'rcpt-ok', 'pending' => 'rcpt-warn', 'failed' => 'rcpt-bad', 'created' => ''][$status];
    @endphp

    <div class="rcpt-page">
        <div class="rcpt-sheet">
            <div class="rcpt">

                <h1 class="rcpt-title">{{ mb_strtoupper($journal) }}</h1>
                <p class="rcpt-sub">Article Processing Charge Receipt</p>
                <hr class="rcpt-rule">

                {{-- Status banner --}}
                <table class="rcpt-banner is-{{ $status }}">
                    <tr>
                        <td class="rcpt-b-pill"><span class="rcpt-pill">{{ $banner[0] }}</span></td>
                        <td>
                            <div class="rcpt-msg-t">{{ $banner[1] }}</div>
                            <div class="rcpt-msg">{{ $banner[2] }}</div>
                        </td>
                        <td class="rcpt-b-amt">
                            <span class="rcpt-amt-box">
                                <div class="rcpt-amt-l">{{ $banner[3] }}</div>
                                <div class="rcpt-amt-v">{{ $amount }}</div>
                            </span>
                        </td>
                    </tr>
                </table>

                {{-- Article summary --}}
                <h2 class="rcpt-h">Article Summary</h2>
                <table class="rcpt-grid">
                    <tr>
                        <td class="k">Journal</td>
                        <td class="v">{{ $journal }}</td>
                        <td class="k">Article ID</td>
                        <td class="v mono">{{ $v('article_uid') }}</td>
                    </tr>
                    <tr>
                        <td class="k">Manuscript Title</td>
                        <td class="b" colspan="3">{{ $v('article_title') }}</td>
                    </tr>
                    <tr>
                        <td class="k">Date of Submission</td>
                        <td class="v">{{ $submitted }}</td>
                        <td class="k">Processing Charge</td>
                        <td class="v b">{{ $amount }}</td>
                    </tr>
                </table>

                {{-- Author --}}
                <h2 class="rcpt-h">Author Information</h2>
                <table class="rcpt-grid">
                    <tr>
                        <td class="k">Author Name</td>
                        <td class="v b">{{ $v('author_name') }}</td>
                        <td class="k">Email</td>
                        <td class="v">{{ $v('author_email') }}</td>
                    </tr>
                </table>

                {{-- Transaction --}}
                <h2 class="rcpt-h">Transaction Information</h2>
                <table class="rcpt-grid">
                    <tr>
                        <td class="k">Receipt No</td>
                        <td class="v mono">{{ $v('receipt_no') }}</td>
                        <td class="k">Transaction Status</td>
                        <td class="v {{ $statusClass }}">{{ mb_strtoupper($label) }}</td>
                    </tr>
                    <tr>
                        <td class="k">Order / Reference No</td>
                        <td class="v mono">{{ $v('order_id') }}</td>
                        <td class="k">Transaction ID</td>
                        <td class="v mono">{{ $v('txn_id') }}</td>
                    </tr>
                    <tr>
                        <td class="k">Bank Transaction ID</td>
                        <td class="v mono">{{ $v('bank_txn_id') }}</td>
                        <td class="k">Payment Mode</td>
                        <td class="v b">{{ $v('payment_mode') }}</td>
                    </tr>
                    <tr>
                        <td class="k">Amount</td>
                        <td class="v b">{{ $amount }}</td>
                        <td class="k">Date &amp; Time</td>
                        <td class="v">{{ $v('date') }}</td>
                    </tr>
                </table>

                <div class="rcpt-foot">
                    This is a computer generated official payment receipt from {{ $journal }}. No physical signature is required.<br>
                    Generated on {{ now()->format('d M Y, h:i A') }}
                </div>
            </div>
        </div>

        <div class="rcpt-actions">
            <button type="button" class="btn btn-light border" onclick="window.print()">
                <i class="fa fa-print"></i> Print
            </button>
            <a class="btn btn-primary" href="{{ url('/admin/submit-articles') }}">Back to submissions</a>
        </div>
    </div>
@endsection