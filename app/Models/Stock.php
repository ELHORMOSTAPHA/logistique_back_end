<?php

namespace App\Models;

use App\Models\Concerns\RecordsDeletedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'date_arrivage_prevu',//Date prévisionnelle de livraison
        'date_arrivage_reelle',//Date réelle de livraison
        'date_affectation',//Date réelle d'affectation
        'numero_lot',//Numero de lot
        'numero_arrivage',//Numero d'arrivage
        'statut',//etat d'avancement de la livraison
        'created_by',
        'deleted_by',
        'deleted_at',
        'updated_by',
    ];

    protected $casts = [
        'reserved' => 'boolean',
        'expose'   => 'integer',
    ];

    // Relations
    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }
    public function stockStatus(): BelongsTo
    {
        return $this->belongsTo(StockStatus::class);
    }
    // public function lot(): BelongsTo
    // {
    //     return $this->belongsTo(Lot::class);
    // }
    //queries builder
    /**
     * Global keyword search across stock columns and related depot / lot fields (API `name` param).
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
    public function scopeFilterByDate($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
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

