<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\RecordsDeletedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, RecordsDeletedBy, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
    */

    protected $fillable = [
        'id',
        'nom',
        'prenom',
        'email',
        'telephone',
        'id_profile',
        'statut',
        'password',
        'avatar',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'id_profile');
    }

    /**
     * Rights for the user's profile (same profile_id on permissions as user's id_profile).
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'profile_id', 'id_profile');
    }

 

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
      
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
