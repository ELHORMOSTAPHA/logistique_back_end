<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmVehiculeColor extends Model
{
    protected $table = 'crm_vehicules_colors';

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'reference',
        'prix',
        'modele_id',
        'type',
        'hex_color',
    ];

    protected $casts = [
        'prix'      => 'decimal:2',
        'modele_id' => 'integer',
    ];

    public function modele(): BelongsTo
    {
        return $this->belongsTo(CarModele::class, 'modele_id');
    }
}
