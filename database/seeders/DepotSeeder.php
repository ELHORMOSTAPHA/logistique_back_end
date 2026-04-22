<?php

namespace Database\Seeders;

use App\Models\Depot;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepotSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::query()->orderBy('id')->value('id');
        $createdBy = $userId !== null ? (string) $userId : null;

        $depots = [
            ['name' => 'Port', 'type' => 'stockage'],
        ];

        foreach ($depots as $row) {
            Depot::updateOrCreate(
                ['name' => $row['name']],
                [
                    'type' => $row['type'],
                    'created_by' => $createdBy,
                ]
            );
        }
    }
}
