<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleReviewApproval extends Model
{
    protected $fillable = [
        'article_review_id',
        'key',
        'status',
        'remarks',
        'performed_by',
    ];

    public function articleReview()
    {
        return $this->belongsTo(ArticleReview::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}