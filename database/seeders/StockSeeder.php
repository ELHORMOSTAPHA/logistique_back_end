<?php

namespace Database\Seeders;

use App\Models\Depot;
use App\Models\Stock;
use App\Models\StockStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        $userId    = User::query()->orderBy('id')->value('id');
        $depotIds  = Depot::query()->orderBy('id')->pluck('id')->all();
        $statusIds = StockStatus::query()->orderBy('id')->pluck('id')->all();

        $catalog = [
            [
                'modele'    => 'S09',
                'finitions' => ['2.0 4x2 ICE RISE', '2.0 4x4 ICE SUMMIT'],
                'colors_ex' => ['Starlit black', 'Moon gray', 'Snow white', 'Mountain Green', 'Ocean Blue'],
                'colors_int' => ['Ink black', 'Amber brown', 'Spring green'],
            ],
            [
                'modele'    => 'S05',
                'finitions' => ['Summit 4X2 BVA 1.5T+1DHT'],
                'colors_ex' => ['Snow white', 'Phantom gray', 'Cosmic silver'],
                'colors_int' => ['Racing red', 'Sky Blue'],
            ],
            [
                'modele'    => 'S07',
                'finitions' => ['1.5L ICE RISE', '1.6L ICE SUMMIT'],
                'colors_ex' => ['Pearl white', 'Sky blue', 'Ocean blue'],
                'colors_int' => ['Lafite red', 'Porcelain white', 'Obsidian black'],
            ],
        ];

        $n = 0;
        foreach ($catalog as $vehicle) {
            foreach ($vehicle['finitions'] as $finition) {
                foreach ($vehicle['colors_ex'] as $colorEx) {
                    foreach ($vehicle['colors_int'] as $colorInt) {
                        $n++;
                        // Every other entry is an arrivage placeholder (no VIN)
                        $hasVin    = $n % 2 !== 0;
                        $vin       = $hasVin ? ('SEED' . str_pad((string) $n, 13, '0', STR_PAD_LEFT)) : null;
                        $depotId   = $depotIds  !== [] ? $depotIds[($n - 1)  % count($depotIds)]  : null;
                        $statusId  = $statusIds !== [] ? $statusIds[($n - 1) % count($statusIds)] : null;

                        $data = [
                            'marque'                 => 'Soueast',
                            'modele'                 => $vehicle['modele'],
                            'finition'               => $finition,
                            'color_ex'               => $colorEx,
                            'color_int'              => $colorInt,
                            'numero_commande'        => 'CMD-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
                            'reserved'               => false,
                            'depot_id'               => $depotId,
                            'stock_status_id'        => $statusId,
                            'date_arrivage_prevu'    => now()->addDays(($n % 45) + 5)->toDateString(),
                            'date_arrivage_reelle'   => $hasVin ? now()->subDays($n % 15)->toDateString() : null,
                            'numero_lot'             => 'LOT-2026-' . str_pad((string) (($n % 7) + 1), 3, '0', STR_PAD_LEFT),
                            'numero_arrivage'        => 'ARR-' . str_pad((string) (24000 + $n), 5, '0', STR_PAD_LEFT),
                            'statut'                 => '1',
                            'created_by'             => $userId,
                            'updated_by'             => $userId,
                        ];

                        if ($vin !== null) {
                            Stock::updateOrCreate(['vin' => $vin], $data);
                        } else {
                            // Arrivage placeholder: no VIN uniqueness constraint to worry about
                            Stock::create($data);
                        }
                    }
                }
            }
        }
    }
}
