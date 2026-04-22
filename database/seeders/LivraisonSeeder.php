<?php

namespace Database\Seeders;

use App\Models\Livraison;
use App\Models\LivraisonHistorique;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Seeder;

class LivraisonSeeder extends Seeder
{
    public function run(): void
    {
        $userId   = User::query()->orderBy('id')->value('id');
        $stockIds = Stock::query()->orderBy('id')->pluck('id')->all();

        if (empty($stockIds)) {
            $this->command->warn('No stocks found — skipping LivraisonSeeder.');
            return;
        }

        $clients = [
            'Mohamed Alami',
            'Fatima Benali',
            'Youssef El Idrissi',
            'Sara Tazi',
            'Rachid Moussaoui',
            'Nadia Cherkaoui',
            'Karim Benyahia',
            'Laila Hajji',
        ];

        $rows = [
            // en_attente — no ww/facture yet
            [
                'statut'    => 'en_attente',
                'ww'        => null,
                'n_facture' => null,
                'history'   => [
                    ['statut' => 'en_attente', 'infos' => null],
                ],
            ],
            [
                'statut'    => 'en_attente',
                'ww'        => 'WW136001',
                'n_facture' => null,
                'history'   => [
                    ['statut' => 'en_attente', 'infos' => null],
                ],
            ],
            // facturé — ww set, facture issued
            [
                'statut'    => 'facturé',
                'ww'        => 'WW136582',
                'n_facture' => 'FA54214525001',
                'history'   => [
                    ['statut' => 'en_attente', 'infos' => null],
                    ['statut' => 'facturé',    'infos' => 'FA54214525001'],
                ],
            ],
            [
                'statut'    => 'facturé',
                'ww'        => 'WW137001',
                'n_facture' => 'FA54214525002',
                'history'   => [
                    ['statut' => 'en_attente', 'infos' => null],
                    ['statut' => 'facturé',    'infos' => 'FA54214525002'],
                ],
            ],
            // livré — full flow
            [
                'statut'    => 'livré',
                'ww'        => 'WW135000',
                'n_facture' => 'FA54214524900',
                'history'   => [
                    ['statut' => 'en_attente', 'infos' => null],
                    ['statut' => 'facturé',    'infos' => 'FA54214524900'],
                    ['statut' => 'livré',      'infos' => 'WW135000'],
                ],
            ],
            [
                'statut'    => 'livré',
                'ww'        => 'WW134500',
                'n_facture' => 'FA54214524500',
                'history'   => [
                    ['statut' => 'en_attente', 'infos' => null],
                    ['statut' => 'facturé',    'infos' => 'FA54214524500'],
                    ['statut' => 'livré',      'infos' => 'WW134500'],
                ],
            ],
            // extra en_attente entries
            [
                'statut'    => 'en_attente',
                'ww'        => 'WW138000',
                'n_facture' => null,
                'history'   => [
                    ['statut' => 'en_attente', 'infos' => null],
                ],
            ],
            [
                'statut'    => 'facturé',
                'ww'        => 'WW138200',
                'n_facture' => 'FA54214525300',
                'history'   => [
                    ['statut' => 'en_attente', 'infos' => null],
                    ['statut' => 'facturé',    'infos' => 'FA54214525300'],
                ],
            ],
        ];

        foreach ($rows as $i => $row) {
            $stockId = $stockIds[$i % count($stockIds)];

            $livraison = Livraison::query()->create([
                'stock_id'   => $stockId,
                'client'     => $clients[$i % count($clients)],
                'statut'     => $row['statut'],
                'ww'         => $row['ww'],
                'n_facture'  => $row['n_facture'],
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            foreach ($row['history'] as $h) {
                LivraisonHistorique::query()->create([
                    'livraison_id' => $livraison->id,
                    'statut'       => $h['statut'],
                    'infos'        => $h['infos'],
                    'created_by'   => $userId,
                ]);
            }
        }

        $this->command->info('LivraisonSeeder: ' . count($rows) . ' livraisons seeded.');
    }
}
