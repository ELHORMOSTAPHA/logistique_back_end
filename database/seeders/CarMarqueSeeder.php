<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarMarqueSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'Soueast', 'status' => 1],
        ];

        foreach ($rows as $row) {
            DB::table('car_marques')->updateOrInsert(
                ['id' => $row['id']],
                $row
            );
        }
    }
}
