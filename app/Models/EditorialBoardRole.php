<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EditorialBoardRole extends Model
{
    use HasFactory;

    protected $table = 'editorial_board_roles';

    protected $fillable = [
        'journal_id',
        'role',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }
}