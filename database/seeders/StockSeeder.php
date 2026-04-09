<?php

namespace Database\Seeders;

use App\Models\Depot;
use App\Models\Lot;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::query()->orderBy('id')->value('id');
        $lotIds = Lot::query()->orderBy('id')->pluck('id')->all();
        $depotIds = Depot::query()->orderBy('id')->pluck('id')->all();

        if ($lotIds === []) {
            $this->command?->warn('StockSeeder: aucun lot — exécutez LotSeeder avant.');

            return;
        }

        $marques = ['Renault', 'Peugeot', 'Dacia', 'Citroën'];
        $modeles = ['Clio', '208', 'Sandero', 'C3', 'Megane', '3008'];
        $colorsEx = ['Blanc Nacré', 'Gris Platine', 'Noir Métal', 'Bleu Iron'];
        $colorsExCodes = ['#f3f3ee', '#b0b4b9', '#252525', '#4f6f94'];
        $colorsIn = ['Tissu gris', 'Cuir noir', 'Tissu noir'];
        $colorsInCodes = ['#8f959e', '#1c1c1c', '#252525'];

        $n = 0;
        foreach ($lotIds as $lotIndex => $lotId) {
            $count = $lotIndex === 0 ? 6 : 4;
            for ($k = 0; $k < $count; $k++) {
                $n++;
                $vin = $this->fakeVin($n);
                $depotId = $depotIds !== [] ? $depotIds[($n - 1) % count($depotIds)] : null;

                Stock::updateOrCreate(
                    ['vin' => $vin],
                    [
                        'modele' => $modeles[($n - 1) % count($modeles)],
                        'version' => 'Intens',
                        'marque' => $marques[($n - 1) % count($marques)],
                        'color_ex' => $colorsEx[($n - 1) % count($colorsEx)],
                        'color_ex_code' => $colorsExCodes[($n - 1) % count($colorsExCodes)],
                        'color_int' => $colorsIn[($n - 1) % count($colorsIn)],
                        'color_int_code' => $colorsInCodes[($n - 1) % count($colorsInCodes)],
                        'reserved' => $n % 7 === 0,
                        'depot_id' => $depotId,
                        'lot_id' => $lotId,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]
                );
            }
        }
    }

    private function fakeVin(int $n): string
    {
        return 'SEED'.str_pad((string) $n, 13, '0', STR_PAD_LEFT);
    }
}
