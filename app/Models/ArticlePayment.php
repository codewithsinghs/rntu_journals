<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticlePayment extends Model
{
    protected $fillable = [
        'submit_article_id',
        'user_id',
        'gateway',
        'order_id',
        'txn_id',
        'bank_txn_id',
        'payment_mode',
        'amount',
        'currency',
        'status',
        'failure_reason',
        'response',
        'paid_at',
    ];

    protected $casts = [
        'amount'   => 'decimal:2',
        'response' => 'array',
        'paid_at'  => 'datetime',
    ];

    public function article()
    {
        return $this->belongsTo(SubmitArticle::class, 'submit_article_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** e.g. RCPT-20261022-000012 (null-safe if created_at wasn't selected) */
    public function getReceiptNoAttribute(): string
    {
        $date = $this->created_at ?? now();

        return 'RCPT-' . $date->format('Ymd') . '-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function toReceipt(): array
    {
        return [
            'receipt_no'     => $this->receipt_no,
            'order_id'       => $this->order_id,
            'txn_id'         => $this->txn_id,
            'bank_txn_id'    => $this->bank_txn_id,
            'payment_mode'   => $this->payment_mode,
            'amount'         => $this->amount,
            'currency'       => $this->currency,
            'status'         => $this->status,   // paid | pending | failed | created
            'failure_reason' => $this->failure_reason,
            'article_uid'    => $this->article->article_uid ?? '-',
            'article_title'  => $this->article->manuscript_title ?? '-',
            'date'           => ($this->paid_at ?? $this->created_at ?? now())->format('d M Y, h:i A'),
        ];
    }
}