<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleReviewer extends Model
{
    protected $table = 'article_reviewers';

    protected $fillable = [
        'submit_article_id',
        'name',
        'email',
        'institution',
        'area_of_expertise',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────────────

    public function article(): BelongsTo
    {
        return $this->belongsTo(SubmitArticle::class, 'submit_article_id');
    }
}