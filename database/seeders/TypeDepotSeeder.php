<?php

namespace Database\Seeders;

use App\Models\TypeDepot;
use Illuminate\Database\Seeder;

class TypeDepotSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['libelle' => 'Stockage'],
            ['libelle' => 'Showroom'],
            ['libelle' => 'Quarantaine'],
        ];

        foreach ($types as $row) {
            TypeDepot::updateOrCreate(
                ['libelle' => $row['libelle']],
                $row
            );
        }
    }
}
