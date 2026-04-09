<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            ['nom' => 'admin', 'libelle' => 'Administrateur', 'statut' => 'actif'],
            ['nom' => 'commercial', 'libelle' => 'Commercial', 'statut' => 'actif'],
            ['nom' => 'logistique', 'libelle' => 'Logistique / Entrepôt', 'statut' => 'actif'],
            ['nom' => 'lecture', 'libelle' => 'Consultation', 'statut' => 'actif'],
        ];

        foreach ($profiles as $row) {
            Profile::updateOrCreate(
                ['nom' => $row['nom']],
                [
                    'libelle' => $row['libelle'],
                    'statut' => $row['statut'],
                ]
            );
        }
    }
}
