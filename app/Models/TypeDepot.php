<?php

namespace App\Models;

use App\Models\Concerns\RecordsDeletedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeDepot extends Model
{
    use RecordsDeletedBy, SoftDeletes;

    protected $table = 'type_depots';

    protected $fillable = [
        'libelle',
        'created_by',
        'deleted_by',
        'updated_by',
    ];

    public function depots(): HasMany
    {
        return $this->hasMany(Depot::class);
    }
}
