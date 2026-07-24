<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomeBasicContent extends Model
{
    protected $table = 'home_basic_contents';

    protected $fillable = [
        // Aim & Scope
        'aim_and_scope_title_1',
        'aim_and_scope_title_2',
        'aim_and_scope_title_3',
        'aim_and_scope_description',
        'scope_of_publication_description',
        'university_highlight_quote',
        'aim_section_image',

        // Why RNTU Stats
        'why_rntu_title_1',
        'why_rntu_title_2',
        'why_rntu_years',
        'why_rntu_years_label',
        'why_rntu_articles',
        'why_rntu_articles_label',
        'why_rntu_journals',
        'why_rntu_journals_label',
        'why_rntu_readers',
        'why_rntu_readers_label',
        'why_rntu_access',
        'why_rntu_access_label',

        // Support Section
        'support_section_heading',
        'support_articles_count',
        'support_short_heading',
        'support_section_description',

        // Latest Journal Section
        'latest_journal_title',
        'latest_journal_heading',
        'latest_journal_description',

        // Footer
        'footer_about_description',
    ];

    protected $appends = ['aim_section_image_url'];

    /**
     * Always expose the full public URL for the image.
     * Returns null if no image is stored.
     */
    public function getAimSectionImageUrlAttribute(): ?string
    {
        return $this->aim_section_image
            ? asset('storage/' . $this->aim_section_image)
            : null;
    }
}