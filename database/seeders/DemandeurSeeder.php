<?php

namespace Database\Seeders;

use App\Models\Demandeur;
use Illuminate\Database\Seeder;

class DemandeurSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'full_name' => 'Jean Dupont',
                'id_demandeur' => 'DM-EXT-001',
                'site_demandeur' => 'Paris Nord',
                'profile' => 'Fleet',
            ],
            [
                'full_name' => 'Marie Martin',
                'id_demandeur' => 'DM-EXT-002',
                'site_demandeur' => 'Lyon',
                'profile' => 'Retail',
            ],
            [
                'full_name' => 'SARL Transport Plus',
                'id_demandeur' => 'DM-EXT-003',
                'site_demandeur' => 'Marseille',
                'profile' => 'B2B',
            ],
        ];

        foreach ($rows as $attrs) {
            Demandeur::query()->firstOrCreate(
                ['id_demandeur' => $attrs['id_demandeur']],
                $attrs
            );
        }
    }
}
