<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    private const PASSWORD = '12345678';

    public function run(): void
    {
        $adminId = Profile::where('nom', 'admin')->value('id');
        $commercialId = Profile::where('nom', 'commercial')->value('id');
        $logistiqueId = Profile::where('nom', 'logistique')->value('id');
        $lectureId = Profile::where('nom', 'lecture')->value('id');

        $rows = [
            ['email' => 'mostapha.dev.elhor@gmail.com', 'nom' => 'Elhor', 'prenom' => 'Mostapha', 'telephone' => '0612340001', 'id_profile' => $adminId],
            ['email' => 'sophie.martin@example.test', 'nom' => 'Martin', 'prenom' => 'Sophie', 'telephone' => '0612340002', 'id_profile' => $commercialId],
            ['email' => 'karim.benali@example.test', 'nom' => 'Benali', 'prenom' => 'Karim', 'telephone' => '0612340003', 'id_profile' => $logistiqueId],
            ['email' => 'laura.dupont@example.test', 'nom' => 'Dupont', 'prenom' => 'Laura', 'telephone' => '0612340004', 'id_profile' => $commercialId],
            ['email' => 'youssef.idrissi@example.test', 'nom' => 'Idrissi', 'prenom' => 'Youssef', 'telephone' => '0612340005', 'id_profile' => $logistiqueId],
            ['email' => 'emma.rousseau@example.test', 'nom' => 'Rousseau', 'prenom' => 'Emma', 'telephone' => '0612340006', 'id_profile' => $lectureId],
            ['email' => 'mehdi.amrani@example.test', 'nom' => 'Amrani', 'prenom' => 'Mehdi', 'telephone' => '0612340007', 'id_profile' => $commercialId],
            ['email' => 'julie.bernard@example.test', 'nom' => 'Bernard', 'prenom' => 'Julie', 'telephone' => '0612340008', 'id_profile' => $logistiqueId],
            ['email' => 'omar.tazi@example.test', 'nom' => 'Tazi', 'prenom' => 'Omar', 'telephone' => '0612340009', 'id_profile' => $adminId],
            ['email' => 'claire.moreau@example.test', 'nom' => 'Moreau', 'prenom' => 'Claire', 'telephone' => '0612340010', 'id_profile' => $lectureId],
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
