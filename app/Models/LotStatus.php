<?php

namespace App\Models;

use App\Models\Concerns\RecordsDeletedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LotStatus extends Model
{
    use RecordsDeletedBy, SoftDeletes;

    protected $table = 'lot_statuses';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'lot_statuscol',
    ];
    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class);
    }
}
