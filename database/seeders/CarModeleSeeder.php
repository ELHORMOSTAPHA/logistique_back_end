<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarModeleSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'S06', 'marque_id' => 1, 'status' => 1],
            ['id' => 2, 'name' => 'S06 DM', 'marque_id' => 1, 'status' => 1],
            ['id' => 3, 'name' => 'S07', 'marque_id' => 1, 'status' => 1],
            ['id' => 4, 'name' => 'S09', 'marque_id' => 1, 'status' => 1],
            ['id' => 5, 'name' => 'S05', 'marque_id' => 1, 'status' => 1],
            ['id' => 334, 'name' => 'S08 DM', 'marque_id' => 1, 'status' => 1],
        ];

        foreach ($rows as $row) {
            DB::table('car_modeles')->updateOrInsert(
                ['id' => $row['id']],
                $row
            );
        }
    }
}
