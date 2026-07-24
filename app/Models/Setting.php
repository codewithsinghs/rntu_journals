<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'address',
        'email',
        'phone',
        'website_name',
        'website_url',
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'youtube_url',
        'linkedin_url',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function mediaSlots()
    {
        return $this->hasMany(SettingMedia::class, 'settings_id', 'id');
    }

    public function getMedia(string $key): ?Media
    {
        if ($this->relationLoaded('mediaSlots')) {
            return $this->mediaSlots->firstWhere('key', $key)?->media;
        }

        return $this->mediaSlots()->where('key', $key)->first()?->media;
    }

    public function setMedia(string $key, int $mediaId): SettingMedia
    {
        return $this->mediaSlots()->updateOrCreate(
            ['key'      => $key],
            ['media_id' => $mediaId]
        );
    }

    public function removeMedia(string $key): void
    {
        $this->mediaSlots()->where('key', $key)->delete();
    }

    public function getLogoAttribute(): ?Media
    {
        return $this->getMedia('logo');
    }

    public function getFaviconAttribute(): ?Media
    {
        return $this->getMedia('favicon');
    }
}