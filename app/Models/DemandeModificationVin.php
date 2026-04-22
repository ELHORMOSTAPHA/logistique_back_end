<?php

namespace App\Models;

use App\Models\Concerns\RecordsDeletedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemandeModificationVin extends Model
{
    use RecordsDeletedBy, SoftDeletes;

    protected $table = 'demande_modification_vins';

    protected $fillable = [
        'demandes_reservation_id',
        'stock_id',
        'demandeur_id',
        'vin_initial',
        'vin_nouveau',
        'motif',
        'statut',
        'valideur_id',
        'validated_at',
        'motif_refus',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    public function demandeReservation(): BelongsTo
    {
        return $this->belongsTo(DemandeReservation::class, 'demandes_reservation_id');
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function demandeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'demandeur_id');
    }

    public function valideur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valideur_id');
    }
}
