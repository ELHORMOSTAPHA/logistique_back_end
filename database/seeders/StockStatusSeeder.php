<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockStatusSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['libelle' => 'En fabrication', 'is_available_for_update' => true],
            ['libelle' => 'En acheminement', 'is_available_for_update' => true],
            ['libelle' => 'Arrivé au port', 'is_available_for_update' => true],
            ['libelle' => 'Entrée en stock', 'is_available_for_update' => true],
            ['libelle' => 'Livrée', 'is_available_for_update' => false],
            ['libelle' => 'Facturée', 'is_available_for_update' => false],
        ];

        foreach ($rows as $row) {
            DB::table('stock_statuts')->updateOrInsert(['libelle' => $row['libelle']], $row);
        }
    }
}
