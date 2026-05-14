<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
