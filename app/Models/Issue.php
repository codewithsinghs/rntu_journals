<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

// use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Issue extends Model
{
    // use HasFactory,HasUuids;
    use HasFactory;

    protected $table = 'issues';
    protected $fillable = [
        'uuid',
        'journal_id',
        'volume_id',
        'issue',
        'year',
        'published_date',
        'status',
        'is_current',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];
     protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function resolveRouteBinding($value, $field = null)
{
    return $this->where('uuid', $value)
        ->orWhere($this->getKeyName(), $value)
        ->firstOrFail();
}

// public function resolveRouteBinding($value, $field = null)
// {
//     $query = $this->newQuery();

//     if (Str::isUuid($value)) {
//         return $query->where('uuid', $value)->firstOrFail();
//     }

//     return $query->where($this->getKeyName(), $value)->firstOrFail();
// }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    public function volume(): BelongsTo
    {
        return $this->belongsTo(Volume::class, 'volume_id');
    }

    public function getRouteKeyName()
{
    return 'uuid';
}

}