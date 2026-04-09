<?php

namespace App\Models;

use App\Models\Concerns\RecordsDeletedBy;
use App\Models\LotStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lot extends Model
{
    use RecordsDeletedBy, SoftDeletes;

    protected $fillable = [
        'numero_lot',
        'numero_arrivage',
        'statut',
        'date_arrivage_prevu',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'date_arrivage_prevu' => 'date',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
    public function lotStatus(): BelongsTo
    {
        return $this->belongsTo(LotStatus::class);
    }
}

