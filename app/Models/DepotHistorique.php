<?php

namespace App\Models;

use App\Models\Concerns\RecordsDeletedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepotHistorique extends Model
{
    use RecordsDeletedBy, SoftDeletes;

    protected $table = 'depot_historiques';

    public $timestamps = false;

    protected $fillable = [
        'created_by',
        'stock_id',
        'depot_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'date',
        ];
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }
}
