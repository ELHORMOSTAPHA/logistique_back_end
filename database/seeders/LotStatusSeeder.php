<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LotStatusSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'En fabrication', 'lot_statuscol' => '1'],
            ['name' => 'En acheminement', 'lot_statuscol' => '2'],
            ['name' => 'Arrivé au port', 'lot_statuscol' => '3'],
            ['name' => 'Entrée en stock', 'lot_statuscol' => '4'],
        ];

        foreach ($rows as $row) {
            DB::table('lot_statuses')->updateOrInsert(
                ['name' => $row['name']],
                ['lot_statuscol' => $row['lot_statuscol']]
            );
        }
    }
}
