<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarFinition extends Model
{
    protected $table = 'car_finitions';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'modele_id',
    ];

    protected $casts = [
        'modele_id'                            => 'integer',
    ];

    public function modele(): BelongsTo
    {
        return $this->belongsTo(CarModele::class, 'modele_id');
    }
}
