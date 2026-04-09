<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockStatusSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'En fabrication', 'stock_status' => '1'],
            ['name' => 'En acheminement', 'stock_status' => '2'],
            ['name' => 'Arrivé au port', 'stock_status' => '3'],
            ['name' => 'Entrée en stock', 'stock_status' => '4'],
        ];

        foreach ($rows as $row) {
            DB::table('stock_statuses')->updateOrInsert(
                ['name' => $row['name']],
                ['stock_status' => $row['stock_status']]
            );
        }
    }
}
