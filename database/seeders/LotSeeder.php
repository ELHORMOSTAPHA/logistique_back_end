<?php

namespace Database\Seeders;

use App\Models\Lot;
use App\Models\User;
use Illuminate\Database\Seeder;

class LotSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::query()->orderBy('id')->value('id');

        $lots = [
            [
                'numero_lot' => 'LOT-2026-001',
                'numero_arrivage' => 'ARR-24001',
                'statut' => '4',
                'date_arrivage_prevu' => now()->subDays(10)->toDateString(),
            ],
            [
                'numero_lot' => 'LOT-2026-002',
                'numero_arrivage' => 'ARR-24002',
                'statut' => '3',
                'date_arrivage_prevu' => now()->addDays(5)->toDateString(),
            ],
            [
                'numero_lot' => 'LOT-2026-003',
                'numero_arrivage' => 'ARR-24003',
                'statut' => '2',
                'date_arrivage_prevu' => now()->addDays(20)->toDateString(),
            ],
            [
                'numero_lot' => 'LOT-2026-004',
                'numero_arrivage' => 'ARR-24004',
                'statut' => '1',
                'date_arrivage_prevu' => now()->addDays(45)->toDateString(),
            ],
            [
                'numero_lot' => 'LOT-2026-005',
                'numero_arrivage' => 'ARR-24005',
                'statut' => '4',
                'date_arrivage_prevu' => now()->subDays(3)->toDateString(),
            ],
        ];

        foreach ($lots as $data) {
            Lot::updateOrCreate(
                ['numero_lot' => $data['numero_lot']],
                array_merge($data, [
                    'created_by' => $userId !== null ? (string) $userId : null,
                    'updated_by' => $userId,
                ])
            );
        }
    }
}
