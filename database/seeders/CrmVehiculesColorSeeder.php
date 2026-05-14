<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CrmVehiculesColorSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'nom' => 'Velocity blue', 'reference' => 'bl', 'prix' => 0.00, 'modele_id' => 2, 'type' => 'int', 'hex_color' => '#1F75FE'],
            ['id' => 2, 'nom' => 'Energetic grey', 'reference' => 'eny', 'prix' => 0.00, 'modele_id' => 2, 'type' => 'int', 'hex_color' => '#6E6E6E'],
            ['id' => 4, 'nom' => 'Starlit black', 'reference' => 'sb', 'prix' => 0.00, 'modele_id' => 2, 'type' => 'ext', 'hex_color' => '#0A0A0C'],
            ['id' => 5, 'nom' => 'Phantom Gray', 'reference' => 'pg', 'prix' => 0.00, 'modele_id' => 2, 'type' => 'ext', 'hex_color' => '#4B4B4B'],
            ['id' => 7, 'nom' => 'Moon Grey', 'reference' => 'mg', 'prix' => 0.00, 'modele_id' => 2, 'type' => 'ext', 'hex_color' => '#B6B6B4'],
            ['id' => 8, 'nom' => 'Cosmic Silver', 'reference' => 'cs', 'prix' => 0.00, 'modele_id' => 2, 'type' => 'ext', 'hex_color' => '#C0C0C0'],
            ['id' => 13, 'nom' => 'Starlit black', 'reference' => 'sb', 'prix' => 0.00, 'modele_id' => 1, 'type' => 'ext', 'hex_color' => '#0A0A0C'],
            ['id' => 14, 'nom' => 'Phantom Gray', 'reference' => 'pg', 'prix' => 0.00, 'modele_id' => 1, 'type' => 'ext', 'hex_color' => '#4B4B4B'],
            ['id' => 15, 'nom' => 'Aurora green', 'reference' => 'Ag', 'prix' => 0.00, 'modele_id' => 1, 'type' => 'ext', 'hex_color' => '#0F3B2E'],
            ['id' => 16, 'nom' => 'Starlit black', 'reference' => 'sb', 'prix' => 0.00, 'modele_id' => 3, 'type' => 'ext', 'hex_color' => '#0A0A0C'],
            ['id' => 65, 'nom' => 'Stream Silver', 'reference' => 'SS', 'prix' => 0.00, 'modele_id' => 334, 'type' => 'ext', 'hex_color' => '#C0C0C0'],
            ['id' => 66, 'nom' => 'Pearl White', 'reference' => 'PW', 'prix' => 0.00, 'modele_id' => 334, 'type' => 'ext', 'hex_color' => '#F8F6F0'],
            ['id' => 19, 'nom' => 'Phantom Gray', 'reference' => 'fg', 'prix' => 0.00, 'modele_id' => 3, 'type' => 'ext', 'hex_color' => '#4B4B4B'],
            ['id' => 20, 'nom' => 'Snow white', 'reference' => 'snw', 'prix' => 0.00, 'modele_id' => 3, 'type' => 'ext', 'hex_color' => '#F4F6F9'],
            ['id' => 21, 'nom' => 'Ash Brown', 'reference' => 'ab', 'prix' => 0.00, 'modele_id' => 3, 'type' => 'ext', 'hex_color' => '#B2A38B'],
            ['id' => 22, 'nom' => 'Ocean blue', 'reference' => 'ob', 'prix' => 0.00, 'modele_id' => 3, 'type' => 'ext', 'hex_color' => '#0077BE'],
            ['id' => 26, 'nom' => 'Spring green', 'reference' => 'spb', 'prix' => 0.00, 'modele_id' => 4, 'type' => 'int', 'hex_color' => '#00FF7F'],
            ['id' => 27, 'nom' => 'Amber brown', 'reference' => 'amb', 'prix' => 0.00, 'modele_id' => 4, 'type' => 'int', 'hex_color' => '#8B4513'],
            ['id' => 64, 'nom' => 'Deep Blue', 'reference' => 'DB', 'prix' => 0.00, 'modele_id' => 334, 'type' => 'ext', 'hex_color' => '#0B3D91'],
            ['id' => 61, 'nom' => 'Black', 'reference' => 'bl', 'prix' => 0.00, 'modele_id' => 334, 'type' => 'int', 'hex_color' => '#000000'],
            ['id' => 62, 'nom' => 'Azure Green', 'reference' => 'AG', 'prix' => 0.00, 'modele_id' => 334, 'type' => 'ext', 'hex_color' => '#4FAF8F'],
            ['id' => 31, 'nom' => 'Phantom Grey', 'reference' => 'pg', 'prix' => 0.00, 'modele_id' => 4, 'type' => 'ext', 'hex_color' => '#4B4B4B'],
            ['id' => 63, 'nom' => 'Starlit Black', 'reference' => 'SB', 'prix' => 0.00, 'modele_id' => 334, 'type' => 'ext', 'hex_color' => '#0A0A0A'],
            ['id' => 60, 'nom' => 'Camel Brown', 'reference' => 'CB', 'prix' => 0.00, 'modele_id' => 334, 'type' => 'int', 'hex_color' => '#C19A6B'],
            ['id' => 34, 'nom' => 'Ocean Blue', 'reference' => 'ob', 'prix' => 0.00, 'modele_id' => 4, 'type' => 'ext', 'hex_color' => '#1E90FF'],
            ['id' => 35, 'nom' => 'Cosmic Silver', 'reference' => 'CS', 'prix' => 0.00, 'modele_id' => 1, 'type' => 'ext', 'hex_color' => '#C0C0C0'],
            ['id' => 36, 'nom' => 'Moon grey', 'reference' => 'Mg', 'prix' => 0.00, 'modele_id' => 1, 'type' => 'ext', 'hex_color' => '#B6B6B4'],
            ['id' => 37, 'nom' => 'Snow white', 'reference' => 'SNW', 'prix' => 0.00, 'modele_id' => 1, 'type' => 'ext', 'hex_color' => '#F4F6F9'],
            ['id' => 38, 'nom' => 'Aurora green', 'reference' => 'ag', 'prix' => 0.00, 'modele_id' => 2, 'type' => 'ext', 'hex_color' => '#0F3B2E'],
            ['id' => 39, 'nom' => 'Snow white', 'reference' => 'snw', 'prix' => 0.00, 'modele_id' => 2, 'type' => 'ext', 'hex_color' => '#F4F6F9'],
            ['id' => 40, 'nom' => 'Sky blue', 'reference' => 'skb', 'prix' => 0.00, 'modele_id' => 3, 'type' => 'ext', 'hex_color' => '#87CEEB'],
            ['id' => 41, 'nom' => 'Pearl white', 'reference' => 'pw', 'prix' => 0.00, 'modele_id' => 3, 'type' => 'ext', 'hex_color' => '#FDF6F0'],
            ['id' => 42, 'nom' => 'Mountain Green', 'reference' => 'mg', 'prix' => 0.00, 'modele_id' => 4, 'type' => 'ext', 'hex_color' => '#1E4B3B'],
            ['id' => 43, 'nom' => 'Snow white', 'reference' => 'sw', 'prix' => 0.00, 'modele_id' => 4, 'type' => 'ext', 'hex_color' => '#F4F6F9'],
            ['id' => 44, 'nom' => 'Ink black', 'reference' => 'ib', 'prix' => 0.00, 'modele_id' => 4, 'type' => 'int', 'hex_color' => '#0D0D0D'],
            ['id' => 45, 'nom' => 'Starlit black', 'reference' => 'sb', 'prix' => 0.00, 'modele_id' => 5, 'type' => 'ext', 'hex_color' => '#0A0A0C'],
            ['id' => 46, 'nom' => 'Sky Blue', 'reference' => 'skb', 'prix' => 0.00, 'modele_id' => 5, 'type' => 'int', 'hex_color' => '#87CEEB'],
            ['id' => 47, 'nom' => 'Racing red', 'reference' => 'rb', 'prix' => 0.00, 'modele_id' => 5, 'type' => 'int', 'hex_color' => '#CC0000'],
            ['id' => 49, 'nom' => 'Cosmic silver', 'reference' => 'cs', 'prix' => 0.00, 'modele_id' => 5, 'type' => 'ext', 'hex_color' => '#A9A9B2'],
            ['id' => 50, 'nom' => 'Phantom gray', 'reference' => 'pw', 'prix' => 0.00, 'modele_id' => 5, 'type' => 'ext', 'hex_color' => '#6E6E73'],
            ['id' => 51, 'nom' => 'Snow white', 'reference' => 'sw', 'prix' => 0.00, 'modele_id' => 5, 'type' => 'ext', 'hex_color' => '#FFFAFA'],
            ['id' => 52, 'nom' => 'Moon gray', 'reference' => 'mog', 'prix' => 0.00, 'modele_id' => 4, 'type' => 'ext', 'hex_color' => '#C0C0C8'],
            ['id' => 53, 'nom' => 'Starlit black', 'reference' => 'sb', 'prix' => 0.00, 'modele_id' => 4, 'type' => 'ext', 'hex_color' => '#0A0A0F'],
            ['id' => 54, 'nom' => 'Gravity gray', 'reference' => 'gg', 'prix' => 0.00, 'modele_id' => 1, 'type' => 'int', 'hex_color' => '#C3C6C7'],
            ['id' => 55, 'nom' => 'Obsidian black', 'reference' => 'ob', 'prix' => 0.00, 'modele_id' => 1, 'type' => 'int', 'hex_color' => '#0B0B0D'],
            ['id' => 56, 'nom' => 'Solar orange', 'reference' => 'sg', 'prix' => 0.00, 'modele_id' => 1, 'type' => 'int', 'hex_color' => '#FF6B00'],
            ['id' => 57, 'nom' => 'Obsidian black', 'reference' => 'ob', 'prix' => 0.00, 'modele_id' => 3, 'type' => 'int', 'hex_color' => '#0B0B0D'],
            ['id' => 58, 'nom' => 'Porcelain white', 'reference' => 'pw', 'prix' => 0.00, 'modele_id' => 3, 'type' => 'int', 'hex_color' => '#F6F4F1'],
            ['id' => 59, 'nom' => 'Lafite red', 'reference' => 'lr', 'prix' => 0.00, 'modele_id' => 3, 'type' => 'int', 'hex_color' => '#7B1113'],
        ];

        foreach ($rows as $row) {
            DB::table('crm_vehicules_colors')->updateOrInsert(
                ['id' => $row['id']],
                $row
            );
        }
    }
}
