<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Journal extends Model
{
    use HasFactory;

    protected $table = 'journal';

    protected $fillable = [
        // ===== Common fields =====
        'title',
        'slug',
        'description',
        'cover_image',
        'is_active',

        // ===== From journals (unique) =====
        'view_all_issues_label',
        'view_all_issues_link',
        'explore_journals_label',
        'explore_journals_link',
        'title_2',
        'fields_covered',
        'sequence',

        // ===== From journal (unique) =====
        'abbreviation',
        'e_issn',
        'p_issn',
        'issn_online',
        'volume',
        'issue',
        'latest_volume',
        'publication_language',
        'publishing_frequency',
        'publishing_months',
        'indexing_impact_factor',
        'time_to_first_decision',
        'time_to_review',
        'acceptance_to_publication',
        'aim_and_scope_title',
        'aim_and_scope',
        'badge',
        'article_template_url',
    ];

    protected $casts = [
        'fields_covered' => 'array',
        'is_active'      => 'boolean',
        'sequence'       => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($journal) {
            if (empty($journal->slug)) {
                $journal->slug = static::generateUniqueSlug($journal->title);
            }
        });

        static::updating(function ($journal) {
            if (empty($journal->slug) && $journal->isDirty('title')) {
                $journal->slug = static::generateUniqueSlug($journal->title);
            }
        });
    }

    protected static function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $original = $slug;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $count;
            $count++;
        }

        return $slug;
    }

    public function submitArticles(): HasMany
    {
        return $this->hasMany(SubmitArticle::class, 'journal_id');
    }

    public function volumes(): HasMany
    {
        return $this->hasMany(Volume::class, 'journal_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class, 'journal_id');
    }

    public function editorial_board(): HasMany
    {
        return $this->hasMany(EditorialBoard::class, 'journal_id');
    }

    public function guidelines(): HasMany
    {
        return $this->hasMany(Guideline::class, 'journal_id');
    }

    public function editorialboardrole(): HasMany
    {
        return $this->hasMany(EditorialBoardRole::class, 'journal_id');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}