<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    private const PASSWORD = 'ass919';

    public function run(): void
    {
        $adminId = Profile::where('nom', 'admin')->value('id');
        $commercialId = Profile::where('nom', 'commercial')->value('id');
        $logistiqueId = Profile::where('nom', 'logistique')->value('id');
        $lectureId = Profile::where('nom', 'lecture')->value('id');

        $rows = [
            ['email' => 'admin@gmail.com', 'nom' => 'admin', 'prenom' => 'admin', 'telephone' => '0612340001', 'id_profile' => $adminId],
            ['email' => 'commercial@gmail.com', 'nom' => 'Martin', 'prenom' => 'Sophie', 'telephone' => '0612340002', 'id_profile' => $commercialId],
            ['email' => 'logistique@gmail.com', 'nom' => 'Benali', 'prenom' => 'Karim', 'telephone' => '0612340003', 'id_profile' => $logistiqueId],
            ['email' => 'lecture@gmail.com', 'nom' => 'Dupont', 'prenom' => 'Laura', 'telephone' => '0612340004', 'id_profile' => $commercialId],
        ];

        foreach ($rows as $row) {
            User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'nom' => $row['nom'],
                    'prenom' => $row['prenom'],
                    'telephone' => $row['telephone'],
                    'id_profile' => $row['id_profile'],
                    'statut' => 'actif',
                    'password' => self::PASSWORD,
                ]
            );
        }
    }
}
