<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutBasicContent extends Model
{
    protected $table = 'about_basic_contents';

    protected $fillable = [
        'about_badge',
        'about_heading',
        'about_description_1',
        'about_description_2',
        'about_section_img1',
        'about_section_img2',
        'why_badge',
        'why_heading',
        'why_description_1',
        'why_description_2',
        'why_section_image',
    ];

    protected $appends = [
        'why_section_image_url',
        'about_section_img1_url',
        'about_section_img2_url',
    ];

    public function getWhySectionImageUrlAttribute(): ?string
    {
        return $this->why_section_image
            ? asset('storage/' . $this->why_section_image)
            : null;
    }

    public function getAboutSectionImg1UrlAttribute(): ?string
    {
        return $this->about_section_img1
            ? asset('storage/' . $this->about_section_img1)
            : null;
    }

    public function getAboutSectionImg2UrlAttribute(): ?string
    {
        return $this->about_section_img2
            ? asset('storage/' . $this->about_section_img2)
            : null;
    }
}