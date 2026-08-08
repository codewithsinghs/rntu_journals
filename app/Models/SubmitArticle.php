<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
// use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SubmitArticle extends Model
{
    // use HasUuids;
    use SoftDeletes;

    protected $table = 'submit_articles';

    protected $fillable = [
        'uuid',
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
        'issue_id',              // NEW — set when the article is published
        'manuscript_title',
        'abstract_summary',
        'keywords',
        'signed_manuscript_pdf',
        'abstract_file',
        'signature_img',

        // Declaration
        'declarations',

        //references
        'references',

        // Copyright
        'author_signature',
        'submission_date',
        'terms_accepted',
    ];


     protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
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

    public function review()
    {
        return $this->hasOne(ArticleReview::class);
    }

    public function downloads()
    {
        return $this->hasMany(ArticleDownload::class);
    }
}