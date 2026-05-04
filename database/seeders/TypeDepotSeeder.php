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
            ['libelle'=>'Evenement']
        ];

        foreach ($types as $row) {
            TypeDepot::updateOrCreate(
                ['libelle' => $row['libelle']],
                $row
            );
        }
    }
}
