<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Livraison extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'stock_id',
        'client',
        'statut', // en_attente, facturé, livré
        'ww',
        'n_facture',
        'created_by',
        'updated_by',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function livraisonHistoriques(): HasMany
    {
        return $this->hasMany(LivraisonHistorique::class, 'livraison_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
