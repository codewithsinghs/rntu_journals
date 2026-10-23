<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubmitArticle extends Model
{
    // use HasUuids;
    use SoftDeletes;

    protected $table = 'submit_articles';

    protected $fillable = [
        'uuid',
        'article_uid',           // human-readable ID, e.g. ART-2026-00042

        // Ownership
        'user_id',

        // Corresponding author details
        'full_name',
        'mobile_no',
        'email',
        'affiliating_institute',
        'department',
        'orcid_id',
        'affiliating_institute_address',

        // Abstract / manuscript details
        'journal_id',
        'issue_id',              // set when the article is published
        'manuscript_title',
        'abstract_summary',
        'keywords',
        'signed_manuscript_pdf',
        'abstract_file',
        'signature_img',

        // Declaration
        'declarations',

        // References
        'references',

        // Copyright
        'author_signature',
        'submission_date',
        'terms_accepted',

        // Page range inside the issue (editor-entered or auto-numbered)
        'page_start',
        'page_end',

        // Fee waiver
        'is_waiver',

        // Hide / unhide
        'is_hidden',
        'hidden_at',
        'hidden_by',
    ];

    protected $casts = [
        'keywords'        => 'array',
        'declarations'    => 'array',
        'submission_date' => 'date',
        'terms_accepted'  => 'boolean',
        'is_hidden'       => 'boolean',
        'is_waiver'       => 'boolean',
        'hidden_at'       => 'datetime',
        'page_start'      => 'integer',
        'page_end'        => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        // The row id is only known after insert, so build the ID here.
        static::created(function (SubmitArticle $article) {
            $uid = 'ART-' . $article->created_at->format('Y')
                 . '-' . str_pad($article->id, 5, '0', STR_PAD_LEFT);

            DB::table('submit_articles')
                ->where('id', $article->id)
                ->update(['article_uid' => $uid]);

            $article->setAttribute('article_uid', $uid);
            $article->syncOriginalAttribute('article_uid');
        });
    }

    public function resolveRouteBinding($value, $field = null)
{
    return $this->where('uuid', $value)
        ->orWhere($this->getKeyName(), $value)
        ->firstOrFail();
}

    protected $casts = [
        'keywords'        => 'array',
        'declarations'    => 'array',
        'submission_date' => 'date',
        'terms_accepted'  => 'boolean',
        'is_hidden'       => 'boolean',
        'hidden_at'       => 'datetime',
        'deleted_at'      => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function coAuthors(): HasMany
    {
        return $this->hasMany(ArticleCoAuthor::class)->orderBy('order');
    }

    public function reviewers(): HasMany
    {
        return $this->hasMany(ArticleReviewer::class)->orderBy('order');
    }

    public function review(): HasOne
    {
        return $this->hasOne(ArticleReview::class);
    }

    public function downloads()
    {
        return $this->hasMany(ArticleDownload::class);
    }
}