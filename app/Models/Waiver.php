<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Waiver extends Model
{
    protected $fillable = [
        'submit_articles_id',
        'reason',
        'partial_payment',
        'full_payment',
        'payable_amount',
    ];

    protected $casts = [
        'partial_payment' => 'decimal:2',
        'full_payment'    => 'decimal:2',
    ];

    public function article()
    {
        return $this->belongsTo(SubmitArticle::class, 'submit_articles_id');
    }
}