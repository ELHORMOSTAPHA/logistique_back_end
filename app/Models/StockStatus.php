<?php

namespace App\Models;

use App\Models\Concerns\RecordsDeletedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockStatus extends Model
{
    use RecordsDeletedBy, SoftDeletes;

    protected $table = 'stock_statuses';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'stock_status',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
}
