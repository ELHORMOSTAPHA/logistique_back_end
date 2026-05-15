<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarMarque extends Model
{
    protected $table = 'car_marques';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function modeles(): HasMany
    {
        return $this->hasMany(CarModele::class, 'marque_id');
    }
}
