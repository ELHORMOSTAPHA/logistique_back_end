<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarFinition extends Model
{
    protected $table = 'car_finitions';

    public $timestamps = false;

    /**
     * Toutes les colonnes hors `id` sont gérées par la sync (très nombreux champs prix/remises).
     */
    protected $guarded = ['id'];

    protected $casts = [
        'modele_id'                                 => 'integer',
        'nbr_place'                                 => 'integer',
        'nbr_porte'                                 => 'integer',
        'prix'                                      => 'decimal:2',
        'prix_circulaire_particulier'               => 'decimal:2',
        'prix_circulaire_autre'                     => 'decimal:2',
        'prix_circulaire_lcd'                       => 'decimal:2',
        'prix_circulaire_lld'                       => 'decimal:2',
        'prix_circulaire_pgm'                       => 'decimal:2',
        'remise_vendeur'                            => 'decimal:2',
        'remise_chef_vente'                         => 'decimal:2',
        'remise_directeur_marque'                   => 'decimal:2',
        'remise_directeur_globale'                  => 'decimal:2',
        'remise_vendeur_particulier'                => 'decimal:2',
        'remise_vendeur_societe'                    => 'decimal:2',
        'remise_vendeur_lcd'                        => 'decimal:2',
        'remise_vendeur_lld'                        => 'decimal:2',
        'remise_vendeur_pgm'                        => 'decimal:2',
        'remise_chef_vente_particulier'             => 'decimal:2',
        'remise_chef_vente_societe'                 => 'decimal:2',
        'remise_chef_vente_lcd'                     => 'decimal:2',
        'remise_chef_vente_lld'                     => 'decimal:2',
        'remise_chef_vente_pgm'                     => 'decimal:2',
        'remise_directeur_marque_particulier'       => 'decimal:2',
        'remise_directeur_marque_societe'           => 'decimal:2',
        'remise_directeur_marque_lcd'               => 'decimal:2',
        'remise_directeur_marque_lld'               => 'decimal:2',
        'remise_directeur_marque_pgm'               => 'decimal:2',
        'remise_directeur_globale_particulier'      => 'decimal:2',
        'remise_directeur_globale_societe'          => 'decimal:2',
        'remise_directeur_globale_lcd'              => 'decimal:2',
        'remise_directeur_globale_lld'              => 'decimal:2',
        'remise_directeur_globale_pgm'              => 'decimal:2',
    ];

    public function modele(): BelongsTo
    {
        return $this->belongsTo(CarModele::class, 'modele_id');
    }
}
