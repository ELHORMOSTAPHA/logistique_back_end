<?php

namespace App\Models;

use App\Models\Concerns\RecordsDeletedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Depot extends Model
{
    use RecordsDeletedBy, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'type_depot_id',
        'created_by',
        'deleted_by',
    ];

    public function typeDepot(): BelongsTo
    {
        return $this->belongsTo(TypeDepot::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function scopeFilterByName($query, string $name)
    {
        $name = trim($name);
        if ($name === '') {
            return $query;
        }

        $like = '%'.addcslashes($name, '%_\\').'%';

        return $query->where('name', 'like', $like);
    }

    public function scopeFilterByType($query, string $type)
    {
        $type = trim($type);
        if ($type === '') {
            return $query;
        }

        $like = '%'.addcslashes($type, '%_\\').'%';

        return $query->where('type', 'like', $like);
    }

    public function scopeFilterByDate($query, string $from, string $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}

