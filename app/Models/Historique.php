<?php

namespace App\Models;

use App\Models\Concerns\RecordsDeletedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Historique extends Model
{
    use RecordsDeletedBy, SoftDeletes;

    protected $table = 'historiques';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'table_name',
        'record_id',
        'old_value',
        'new_value',
        'metadata',
        'ip_address',
        'http_method',
        'request_path',
        'user_agent',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
