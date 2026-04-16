<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationClient extends Model
{
    protected $fillable = [
        'name',
        'client_id',
        'client_secret',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];
}
