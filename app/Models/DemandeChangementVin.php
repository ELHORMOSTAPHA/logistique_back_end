<?php

namespace App\Models;

use App\Models\Concerns\RecordsDeletedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemandeChangementVin extends Model
{
    use RecordsDeletedBy, SoftDeletes;

    protected $table = 'demande_changement_vins';

    protected $fillable = [
        'demandeur',
        'valideur',
        'motif',
        'vin_remplace',
        'statut',
        'demandes_reservation_id',
    ];

    public function demandeReservation(): BelongsTo
    {
        return $this->belongsTo(DemandeReservation::class, 'demandes_reservation_id');
    }
}
