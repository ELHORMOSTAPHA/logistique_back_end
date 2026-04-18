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
        $userId = User::query()->orderBy('id')->value('id');
        $depotIds = Depot::query()->orderBy('id')->pluck('id')->all();
        $stockStatusIds = StockStatus::query()->orderBy('id')->pluck('id')->all();

        $marques = ['Soueast', 'KGM', 'JAC', 'Exeed'];
        $modeles = ['s07', 'E30X', 'M3 EV', 'RX'];
        $finitions = ['Intens', 'Confort', 'Luxe', 'Sport'];
        $clients = ['Auto Hall', 'Sopriam', 'M-Automotive', 'Particulier'];
        $colorsEx = ['Blanc Nacré', 'Gris Platine', 'Noir Métal', 'Bleu Iron'];
        $colorsExCodes = ['#f3f3ee', '#b0b4b9', '#252525', '#4f6f94'];
        $colorsIn = ['Tissu gris', 'Cuir noir', 'Tissu noir', 'Cuir beige'];
        $colorsInCodes = ['#8f959e', '#1c1c1c', '#252525', '#d2b48c', '#f5f5dc'];

        $n = 0;
        for ($k = 0; $k < 3; $k++) {
            $n++;
            $vin = $this->fakeVin($n);
            $depotId = $depotIds !== [] ? $depotIds[($n - 1) % count($depotIds)] : null;
            $statusId = $stockStatusIds !== [] ? $stockStatusIds[($n - 1) % count($stockStatusIds)] : null;

            Stock::updateOrCreate(
                ['vin' => $vin],
                [
                    'modele' => $modeles[($n - 1) % count($modeles)],
                    'marque' => $marques[($n - 1) % count($marques)],
                    'numero_commande' => 'CMD-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
                    'client' => $clients[($n - 1) % count($clients)],
                    'type_client' => $n % 4 === 0 ? 'Particulier' : 'Société',
                    'PGEO' => 'PGEO-' . str_pad((string) (($n % 9) + 1), 2, '0', STR_PAD_LEFT),
                    'finition' => $finitions[($n - 1) % count($finitions)],
                    'color_ex' => $colorsEx[($n - 1) % count($colorsEx)],
                    'color_ex_code' => $colorsExCodes[($n - 1) % count($colorsExCodes)],
                    'color_int' => $colorsIn[($n - 1) % count($colorsIn)],
                    'color_int_code' => $colorsInCodes[($n - 1) % count($colorsInCodes)],
                    'options' => 'Pack Vision, GPS, Jantes 17"',
                    'vendeur' => 'Vendeur ' . (($n % 6) + 1),
                    'site_affecte' => 'Site ' . (($n % 3) + 1),
                    'date_creation_commande' => now()->subDays(($n % 60) + 1)->toDateString(),
                    'reserved' => $n % 7 === 0,
                    'depot_id' => $depotId,
                    'stock_status_id' => $statusId,
                    'date_arrivage_prevu' => now()->addDays(($n % 45) + 1)->toDateString(),
                    'date_arrivage_reelle' => $n % 5 === 0 ? now()->subDays($n % 10)->toDateString() : null,
                    'date_affectation' => $n % 4 === 0 ? now()->subDays($n % 8)->toDateString() : null,
                    'numero_lot' => 'LOT-2026-' . str_pad((string) (($n % 7) + 1), 3, '0', STR_PAD_LEFT),
                    'numero_arrivage' => 'ARR-' . str_pad((string) (24000 + $n), 5, '0', STR_PAD_LEFT),
                    'statut' => (string) (($n % 4) + 1),
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]
            );
        }
    }

    private function fakeVin(int $n): string
    {
        return 'SEED' . str_pad((string) $n, 13, '0', STR_PAD_LEFT);
    }
}
