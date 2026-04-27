<?php

namespace Database\Seeders;

use App\Models\Depot;
use App\Models\TypeDepot;
use Illuminate\Database\Seeder;

class DepotSeeder extends Seeder
{
    public function run(): void
    {
        $typeIds = [
            'Stockage' => TypeDepot::query()->where('libelle', 'Stockage')->value('id'),
            'Showroom' => TypeDepot::query()->where('libelle', 'Showroom')->value('id'),
            'Quarantaine' => TypeDepot::query()->where('libelle', 'Quarantaine')->value('id'),
        ];

        $rows = [
            [
                'id' => 9,
                'name' => 'Port',
                'type' => 'Stockage',
                'created_at' => '2026-04-23 16:04:31',
                'updated_at' => '2026-04-23 16:04:31',
            ],
            [
                'id' => 10,
                'name' => 'VITA LLD',
                'type' => 'Stockage',
                'created_at' => '2026-04-23 16:04:42',
                'updated_at' => '2026-04-23 16:04:42',
            ],
            [
                'id' => 11,
                'name' => 'Bondoeng',
                'type' => 'Stockage',
                'created_at' => '2026-04-23 16:05:04',
                'updated_at' => '2026-04-23 16:05:04',
            ],
            [
                'id' => 12,
                'name' => 'ZENATA',
                'type' => 'Stockage',
                'created_at' => '2026-04-23 16:06:42',
                'updated_at' => '2026-04-23 16:06:42',
            ],
            [
                'id' => 13,
                'name' => 'VITA',
                'type' => 'Stockage',
                'created_at' => '2026-04-23 16:06:55',
                'updated_at' => '2026-04-23 16:06:55',
            ],
            [
                'id' => 14,
                'name' => 'TEMARA',
                'type' => 'Stockage',
                'created_at' => '2026-04-23 16:07:12',
                'updated_at' => '2026-04-23 16:07:12',
            ],
            [
                'id' => 15,
                'name' => 'CASA ANFA',
                'type' => 'Stockage',
                'created_at' => '2026-04-23 16:07:27',
                'updated_at' => '2026-04-23 16:07:27',
            ],
            [
                'id' => 16,
                'name' => 'Lissasfa',
                'type' => 'Stockage',
                'created_at' => '2026-04-23 16:07:41',
                'updated_at' => '2026-04-23 16:07:41',
            ],
            [
                'id' => 17,
                'name' => 'Bandoeng',
                'type' => 'Stockage',
                'created_at' => '2026-04-23 16:07:53',
                'updated_at' => '2026-04-23 16:07:53',
            ],
            [
                'id' => 18,
                'name' => 'Sidi Othmane',
                'type' => 'Stockage',
                'created_at' => '2026-04-23 16:08:05',
                'updated_at' => '2026-04-23 16:08:05',
            ],
            [
                'id' => 19,
                'name' => 'Rabat',
                'type' => 'Stockage',
                'created_at' => '2026-04-23 16:08:16',
                'updated_at' => '2026-04-23 16:08:16',
            ],
            [
                'id' => 20,
                'name' => 'Casablanca - Al Masira',
                'type' => 'Stockage',
                'created_at' => '2026-04-23 16:08:51',
                'updated_at' => '2026-04-23 16:08:51',
            ],
            [
                'id' => 21,
                'name' => 'showroom',
                'type' => 'Showroom',
                'created_at' => '2026-04-27 14:07:48',
                'updated_at' => '2026-04-27 14:07:48',
            ],
            [
                'id' => 22,
                'name' => 'Quarantaine',
                'type' => 'Quarantaine',
                'created_at' => '2026-04-27 14:07:59',
                'updated_at' => '2026-04-27 14:07:59',
            ],
        ];

        foreach ($rows as $row) {
            $type = $row['type'];
            Depot::updateOrCreate(
                ['id' => $row['id']],
                [
                    'name' => $row['name'],
                    'type' => $type,
                    'type_depot_id' => $typeIds[$type] ?? null,
                    'created_by' => '1',
                    'deleted_at' => null,
                    'deleted_by' => null,
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ]
            );
        }
    }
}
