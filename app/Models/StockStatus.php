<?php

namespace App\Models;

use App\Models\Concerns\RecordsDeletedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockStatus extends Model
{
    use RecordsDeletedBy, SoftDeletes;

    protected $table = 'stock_statuts';

    public $timestamps = false;

    protected $fillable = [
       'libelle',
       'is_available_for_update',
       'deleted_by',
       'deleted_at',
       'created_at',
       'updated_at',
    ];

    protected $casts = [
        'is_available_for_update' => 'boolean',
    ];
    //stock statuses
    const STATUS_EN_FABRICATION = 1;
    const STATUS_ACHEMINEMENT = 2;
    const STATUS_ARRIVAGE_AU_PORT = 3;
    const STATUS_ENTREE_EN_STOCK = 4;
    const STATUS_LIVREE = 5;
    const STATUS_FACTURE = 6;

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
}
