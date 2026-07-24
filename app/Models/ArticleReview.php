<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleReview extends Model
{
    protected $fillable = [
        'submit_article_id',
        'editor_id', 'editor_status', 'editor_remarks','approval_date',
        'reviewer_id','forwarded_to_reviewer_date', 'reviewer_status', 'reviewer_remarks','reviewer_approval_date',
        'full_text', 'final_status', 'current_stage',
        'is_published', 'published_at', 'revision_count',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function submitArticle()
    {
        return $this->belongsTo(SubmitArticle::class);
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
    
    public function approvals()
{
    return $this->hasMany(ArticleReviewApproval::class)->latest();
}
}