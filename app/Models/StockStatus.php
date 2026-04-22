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
       'deleted_by',
       'deleted_at',
       'created_at',
       'updated_at',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
}
