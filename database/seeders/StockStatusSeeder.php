<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockStatusSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['libelle' => 'En fabrication'],
            ['libelle' => 'En acheminement'],
            ['libelle' => 'Arrivé au port'],
            ['libelle' => 'Entrée en stock'],
        ];

        foreach ($rows as $row) {
            DB::table('stock_statuts')->updateOrInsert(['libelle' => $row['libelle']], $row);
        }
    }
}
