<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class EditorialBoard extends Model
{
    use HasFactory;

    protected $table = 'editorial_board';

    protected $fillable = [
        'role',
        'name',
        'designation',
        'department',
        'institute',
        'university_or_org',
        'city',
        'email',
        'orcid_url',
        'scopus_url',
        'web_of_science_url',
        'profile_image',
        'is_active',
        'sequence',
        'journal_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sequence'  => 'integer',
    ];

    // ── Role constants ───────────────────────────────────────────────────
    const ROLES = [
        'Editor-in-Chief',
        'Managing Editor',
        'Executive Editor',
        'Editors',
        'Associate Editors',
        'Members',
    ];
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence')->orderBy('name');
    }

    
}