<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'blocks',
        'content',
        'meta_title',
        'meta_description',
        'meta_image',
        'status',
        'is_homepage',
        'meta',
    ];

    protected $casts = [
        'blocks'      => 'array',
        'meta'        => 'array',
        'is_homepage' => 'boolean',
    ];
}