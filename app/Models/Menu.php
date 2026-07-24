<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menus';

    protected $fillable = [
        'name',
        'location',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta'      => 'array',
    ];

    /**
     * Active Parent Menu Items
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id')
                    ->whereNull('parent_id')
                    ->where('is_active', true)
                    ->orderBy('order');
    }

    /**
     * All Menu Items
     */
    public function allItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id')
                    ->orderBy('order');
    }
}