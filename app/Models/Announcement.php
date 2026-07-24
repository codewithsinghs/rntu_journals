<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Announcement extends Model
{
    use HasFactory;

    protected $table = 'announcements';

    protected $fillable = [
        'name',
        'attachment',
        'link',
        'sequence',
        'meta',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'meta' => 'array',
    ];

    /**
     * Scope: Order by Sequence
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence', 'asc');
    }
}