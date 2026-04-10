<?php

namespace App\Models;

use App\Models\Concerns\RecordsDeletedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemandeReservation extends Model
{
    use RecordsDeletedBy, SoftDeletes;

    protected $table = 'demandes_reservations';

    protected $fillable = [
        'stock_id',
        'id_demande',
        'nom_commercial',
        'id_commercial',
        'demande_infos',
        'statut',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function demandeMotifs(): HasMany
    {
        return $this->hasMany(DemandeMotif::class, 'demandes_reservation_id');
    }

    public function demandeChangementVins(): HasMany
    {
        return $this->hasMany(DemandeChangementVin::class, 'demandes_reservation_id');
    }
}
