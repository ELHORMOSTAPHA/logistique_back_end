<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\RecordsDeletedBy;   

class Profile extends Model
{
        //use HasFactory, Notifiable, RecordsDeletedBy, SoftDeletes;    
    use HasFactory, Notifiable, RecordsDeletedBy, SoftDeletes;

    protected $fillable = [
        'nom',
        'libelle',
        'statut',//actif/inactif
    ];
    public function users()
    {
        return $this->hasMany(User::class, 'id_profile');
    }   

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];
}