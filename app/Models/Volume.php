<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Volume extends Model
{
    use HasFactory;

    protected $table = 'volumes' ;

    protected $fillable = [
        'journal_id',
        'volume',
        'year',
        'status',
        'published_date',
        'is_current',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'published_date' => 'date'
    ];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class, 'volume_id');
    }
}