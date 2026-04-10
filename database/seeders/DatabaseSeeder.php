<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProfileSeeder::class,
            UserSeeder::class,
            StockStatusSeeder::class,
            DepotSeeder::class,
            StockSeeder::class,
            DepotHistoriqueSeeder::class,
            HistoriqueSeeder::class,
            DemandeReservationSeeder::class,
            DemandeChangementVinSeeder::class,
            DemandeMotifSeeder::class,
        ]);
    }
}
