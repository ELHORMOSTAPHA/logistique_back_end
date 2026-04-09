<?php

namespace Database\Seeders;

use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemandeReservationSeeder extends Seeder
{
    public function run(): void
    {
        $commercial = User::query()->whereHas('profile', fn ($q) => $q->where('nom', 'commercial'))->orderBy('id')->first()
            ?? User::query()->orderBy('id')->skip(1)->first();

        $stocks = Stock::query()->orderBy('id')->limit(4)->get();

        if ($stocks->isEmpty() || $commercial === null) {
            return;
        }

        $demandes = [
            [
                'id_demande' => 'DEM-2026-001',
                'nom_commercial' => $commercial->prenom.' '.$commercial->nom,
                'id_commercial' => $commercial->id,
                'demande_infos' => 'Client fleet — livraison sous 10j',
                'statut' => 'en cours',
            ],
            [
                'id_demande' => 'DEM-2026-002',
                'nom_commercial' => $commercial->prenom.' '.$commercial->nom,
                'id_commercial' => $commercial->id,
                'demande_infos' => 'Option financement',
                'statut' => 'validée',
            ],
            [
                'id_demande' => 'DEM-2026-003',
                'nom_commercial' => $commercial->prenom.' '.$commercial->nom,
                'id_commercial' => $commercial->id,
                'demande_infos' => 'Essai avant achat',
                'statut' => 'en cours',
            ],
        ];

        foreach ($demandes as $i => $meta) {
            $stock = $stocks[$i % $stocks->count()];
            $exists = DB::table('demandes_reservations')
                ->where('stock_id', $stock->id)
                ->where('id_demande', $meta['id_demande'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('demandes_reservations')->insert(array_merge($meta, [
                'stock_id' => $stock->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
