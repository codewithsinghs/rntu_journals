<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $table = 'menu_items';

    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'url',
        'target',
        'order',
        'is_active',
        'hide_on_pages',
        'show_on_pages',
        'meta',
    ];

    protected $casts = [
        'hide_on_pages' => 'array',
        'show_on_pages' => 'array',
    'is_active'     => 'boolean',
        'order'     => 'integer',
        'meta'      => 'array',
    ];

    /**
     * Menu Relationship
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    /**
     * Parent Menu Item
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Active Child Menu Items
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
                    ->where('is_active', true)
                    ->orderBy('order');
    }

    /**
     * All Child Menu Items
     */
    public function allChildren(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
                    ->orderBy('order');
    }

    /**
     * Scope Active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}