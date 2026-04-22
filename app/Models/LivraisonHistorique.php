<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LivraisonHistorique extends Model
{
    protected $fillable = [
        'livraison_id',
        'statut', // en_attente, facturé, livré
        'infos', // N° facture : FA54214525454 OR livré: WW136582
        'created_by',
    ];

    public function livraison(): BelongsTo
    {
        return $this->belongsTo(Livraison::class, 'livraison_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
