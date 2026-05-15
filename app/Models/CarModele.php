<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function finitions(): HasMany
    {
        return $this->hasMany(CarFinition::class, 'modele_id');
    }

    public function colors(): HasMany
    {
        return $this->hasMany(CrmVehiculeColor::class, 'modele_id');
    }
}
