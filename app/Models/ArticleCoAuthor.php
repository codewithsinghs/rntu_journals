<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleCoAuthor extends Model
{
    protected $table = 'article_co_authors';

    protected $fillable = [
        'submit_article_id',
        'name',
        'email',
        'affiliation',
        'orcid_id',
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