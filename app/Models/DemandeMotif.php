<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandeMotif extends Model
{
    protected $table = 'demande_motifs';

    protected $fillable = [
        'demandes_reservation_id',
        'motifs_description',
        'file_path',
        'file_type',
    ];

    public function demandeReservation(): BelongsTo
    {
        return $this->belongsTo(DemandeReservation::class, 'demandes_reservation_id');
    }
}
