<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarModele extends Model
{
    protected $table = 'car_modeles';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'marque_id',
        'status',
    ];

    protected $casts = [
        'marque_id' => 'integer',
        'status'    => 'integer',
    ];

    public function marque(): BelongsTo
    {
        return $this->belongsTo(CarMarque::class, 'marque_id');
    }
}
