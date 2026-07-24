<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingMedia extends Model
{
    protected $table = 'settings_media';

    protected $fillable = [
        'settings_id',
        'key',
        'media_id',
    ];

    public function setting()
    {
        return $this->belongsTo(Setting::class, 'settings_id', 'id');
    }

    public function media()
    {
       return $this->belongsTo(Media::class, 'media_id', 'id');
    }
}