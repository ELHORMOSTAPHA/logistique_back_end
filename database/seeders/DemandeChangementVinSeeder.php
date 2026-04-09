<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemandeChangementVinSeeder extends Seeder
{
    public function run(): void
    {
        $demandeId = DB::table('demandes_reservations')
            ->where('id_demande', 'DEM-2026-001')
            ->value('id');

        if ($demandeId === null) {
            return;
        }

        $exists = DB::table('demande_changement_vins')
            ->where('demandes_reservation_id', $demandeId)
            ->exists();

        if ($exists) {
            return;
        }

        $demandeurId = User::query()->orderBy('id')->value('id');

        DB::table('demande_changement_vins')->insert([
            'demandeur' => $demandeurId,
            'valideur' => null,
            'motif' => 'Erreur saisie VIN commercial',
            'vin_remplace' => 'SEED9999999999999',
            'statut' => 'en attente',
            'demandes_reservation_id' => $demandeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
