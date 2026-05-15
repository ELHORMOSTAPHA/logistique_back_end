<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarFinitionSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'PHEV RISE', 'modele_id' => 2],
            ['id' => 2, 'name' => 'PHEV SUMMIT', 'modele_id' => 2],
            ['id' => 3, 'name' => '1.5L  ICE RISE', 'modele_id' => 1],
            ['id' => 4, 'name' => '1.6L ICE SUMMIT', 'modele_id' => 1],
            ['id' => 5, 'name' => '1.5L ICE RISE', 'modele_id' => 3],
            ['id' => 6, 'name' => '1.6L ICE SUMMIT', 'modele_id' => 3],
            ['id' => 7, 'name' => '2.0 4x2 ICE RISE', 'modele_id' => 4],
            ['id' => 8, 'name' => '2.0 4x4 ICE SUMMIT', 'modele_id' => 4],
            ['id' => 9, 'name' => 'Summit 4X2 BVA 1.5T+1DHT', 'modele_id' => 5],
            ['id' => 10, 'name' => 'SUMMIT', 'modele_id' => 334],
        ];

        foreach ($rows as $row) {
            DB::table('car_finitions')->updateOrInsert(
                ['id' => $row['id']],
                $row
            );
        }
    }
}
