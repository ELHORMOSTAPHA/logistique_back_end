<?php

namespace Database\Seeders;

use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HistoriqueSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::query()->orderBy('id')->value('id');
        $stock = Stock::query()->orderBy('id')->first();

        if ($stock === null) {
            return;
        }

        if (DB::table('historiques')->where('table_name', 'stocks')->where('action', 'create')->exists()) {
            return;
        }

        DB::table('historiques')->insert([
            [
                'user_id' => $userId !== null ? (string) $userId : null,
                'action' => 'create',
                'table_name' => 'stocks',
                'record_id' => $stock->id,
                'old_value' => null,
                'new_value' => json_encode(['vin' => $stock->vin, 'modele' => $stock->modele]),
                'created_by' => $userId,
                'created_at' => now()->subDay(),
            ],
            [
                'user_id' => $userId !== null ? (string) $userId : null,
                'action' => 'update',
                'table_name' => 'stocks',
                'record_id' => $stock->id,
                'old_value' => json_encode(['reserved' => false]),
                'new_value' => json_encode(['reserved' => (bool) $stock->reserved]),
                'created_by' => $userId,
                'created_at' => now(),
            ],
        ]);
    }
}
