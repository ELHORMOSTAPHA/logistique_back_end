<?php

namespace Database\Seeders;

use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepotHistoriqueSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::query()->orderBy('id')->value('id');
        $stocks = Stock::query()->with('depot')->orderBy('id')->limit(5)->get();

        if ($stocks->isEmpty()) {
            return;
        }

        foreach ($stocks as $stock) {
            if ($stock->depot_id === null) {
                continue;
            }

            $exists = DB::table('depot_historiques')
                ->where('stock_id', $stock->id)
                ->where('depot_id', $stock->depot_id)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('depot_historiques')->insert([
                'created_by' => $userId,
                'stock_id' => $stock->id,
                'depot_id' => $stock->depot_id,
                'created_at' => now()->subDays(2),
            ]);
        }
    }
}
