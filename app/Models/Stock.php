<?php

namespace App\Models;

use App\Models\Concerns\RecordsDeletedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use RecordsDeletedBy, SoftDeletes;

    protected $fillable = [
        
        'modele',
        //finition =version
        'finition',
        'marque',
        //numero_chassis=vin
        'vin',
        'numero_commande',//numero_commande
        'client',
        'type_client',
        'PGEO',
        'color_ex',
        'color_ex_code',
        'color_int',
        'color_int_code',
        'options',
        'vendeur',
        'site_affecte',
        'date_creation_commande',
        'reserved',
        'depot_id',
        'stock_status_id',
        'entree_stock_date',
        'etat_avancement',
        'date_arrivage_prevu',//Date prévisionnelle de livraison
        'date_arrivage_reelle',//Date réelle de livraison
        'date_affectation',//Date réelle d'affectation
        'numero_lot',//Numero de lot
        'numero_arrivage',//Numero d'arrivage
        'statut',//etat d'avancement de la livraison
        'created_by',
        'deleted_by',
        //combinaison rare
        'combinaison_rare',
        'expose',
        'expose_date',
        'deleted_at',
        'updated_by',
    ];

    protected $casts = [
        'reserved' => 'boolean',
        'expose'   => 'integer',
        'combinaison_rare' => 'boolean',
        'date_creation_commande' => 'date:Y-m-d',
        'date_arrivage_prevu' => 'date:Y-m-d',
        'date_arrivage_reelle' => 'date:Y-m-d',
        'date_affectation' => 'date:Y-m-d',
        // expose_date / entree_stock_date: pas de cast datetime — évite un décalage en lecture.
    ];

    // Relations
    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    /** Passages du véhicule par les dépôts (traçabilité). */
    public function depotHistoriques(): HasMany
    {
        return $this->hasMany(DepotHistorique::class);
    }
    public function stockStatus(): BelongsTo
    {
        return $this->belongsTo(StockStatus::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function livraison(): HasOne
    {
        return $this->hasOne(Livraison::class);
    }
    // public function lot(): BelongsTo
    // {
    //     return $this->belongsTo(Lot::class);
    // }
    //queries builder
    /**
     * Global keyword search across stock columns and related depot / lot fields (API `name` param).
     * Inclut le n° de lot (`numero_lot`).
     */
    public function scopeFilterByName($query, string $name)
    {
        $name = trim($name);
        if ($name === '') {
            return $query;
        }

        $like = '%'.addcslashes($name, '%_\\').'%';

        return $query->where(function ($q) use ($like, $name) {
            $q->where('modele', 'like', $like)
                ->orWhere('finition', 'like', $like)
                ->orWhere('vin', 'like', $like)
                ->orWhere('numero_commande', 'like', $like)
                ->orWhere('numero_lot', 'like', $like)
                ->orWhere('color_ex', 'like', $like)
                ->orWhere('color_ex_code', 'like', $like)
                ->orWhere('color_int', 'like', $like)
                ->orWhere('color_int_code', 'like', $like);

            if (ctype_digit($name)) {
                $q->orWhere('id', (int) $name);
            }

            $q->orWhereHas('depot', function ($dq) use ($like) {
                $dq->where('name', 'like', $like);
            });
        });
    }
    /**
     * Filtre sur `created_at` : bornes calendaires inclusives.
     * Sans normalisation, une date `to` = `Y-m-d` devient minuit ce jour-là et exclut
     * tout créé le même jour après 00:00:00 (ex. 21:40).
     */
    public function scopeFilterByDate($query, $from, $to)
    {
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->endOfDay();

        return $query->whereBetween('created_at', [$start, $end]);
    }
    public function scopeFilterByModele($query, $modele)
    {
        return $query->where('modele', 'like', '%'.$modele.'%');
    }
    public function scopeFilterByVin($query, $vin)
    {
        return $query->where('vin', 'like', '%'.$vin.'%');
    }
    public function scopeFilterByReserved($query, $reserved)
    {
        return $query->where('reserved', $reserved);
    }
    public function scopeFilterByDepotId($query, $depot_id)
    {
        return $query->where('depot_id', $depot_id);
    }
    public function scopeFilterByLotId($query, $lot_id)
    {
        return $query->where('lot_id', $lot_id);
    }
}

