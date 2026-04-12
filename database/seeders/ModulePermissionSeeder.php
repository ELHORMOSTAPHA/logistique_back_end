<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Permission;
use App\Models\Profile;
use Illuminate\Database\Seeder;

class ModulePermissionSeeder extends Seeder
{
    /**
     * Sidebar modules (keys match front-end menu item `key` in data.ts).
     */
    public function run(): void
    {
        $modules = [
            ['name' => 'dashboards', 'label' => 'Tableau de bord', 'url' => '/dashboard', 'position' => 10],
            ['name' => 'stock', 'label' => 'Stock', 'url' => '/stock', 'position' => 20],
            ['name' => 'demandes vin', 'label' => 'Demandes vin', 'url' => '/demande-reservation', 'position' => 30],
            ['name' => 'depots', 'label' => 'Depots', 'url' => '/depots', 'position' => 40],
            ['name' => 'historique', 'label' => 'Historique', 'url' => '/historique', 'position' => 50],
            ['name' => 'livraison', 'label' => 'Livraison', 'url' => '/livraison', 'position' => 60],
            ['name' => 'users-management', 'label' => 'Gestion des utilisateurs', 'url' => '/parametres/utilisateurs', 'position' => 70],
            ['name' => 'profiles-management', 'label' => 'Gestion des profiles', 'url' => '/parametres/Profiles', 'position' => 80],
        ];

        $idsByName = [];
        foreach ($modules as $row) {
            $m = Module::updateOrCreate(
                ['name' => $row['name']],
                [
                    'label' => $row['label'],
                    'url' => $row['url'],
                    'position' => $row['position'],
                    'icon' => null,
                ]
            );
            $idsByName[$row['name']] = $m->id;
        }

        $adminId = Profile::where('nom', 'admin')->value('id');
        $commercialId = Profile::where('nom', 'commercial')->value('id');
        $logistiqueId = Profile::where('nom', 'logistique')->value('id');
        $lectureId = Profile::where('nom', 'lecture')->value('id');

        $all = array_keys($idsByName);

        foreach ($all as $name) {
            $mid = $idsByName[$name];
            $this->upsertPerm($adminId, $mid, true, true, true, true);
        }

        foreach ($all as $name) {
            $mid = $idsByName[$name];
            $read = ! in_array($name, ['users-management', 'profiles-management'], true);
            $this->upsertPerm($commercialId, $mid, false, $name === 'demandes vin', false, $read);
        }

        foreach ($all as $name) {
            $mid = $idsByName[$name];
            if (in_array($name, ['users-management', 'profiles-management'], true)) {
                $this->upsertPerm($logistiqueId, $mid, false, false, false, false);

                continue;
            }
            $full = in_array($name, ['stock', 'depots', 'historique', 'livraison'], true);
            $demandeUpdate = $name === 'demandes vin';
            $this->upsertPerm(
                $logistiqueId,
                $mid,
                $full,
                $full || $demandeUpdate,
                $full,
                true
            );
        }

        foreach ($all as $name) {
            $mid = $idsByName[$name];
            $this->upsertPerm($lectureId, $mid, false, false, false, true);
        }
    }

    private function upsertPerm(
        ?int $profileId,
        int $moduleId,
        bool $create,
        bool $update,
        bool $delete,
        bool $read
    ): void {
        if (! $profileId) {
            return;
        }

        Permission::updateOrCreate(
            [
                'profile_id' => $profileId,
                'module_id' => $moduleId,
            ],
            [
                'can_create' => $create,
                'can_update' => $update,
                'can_delete' => $delete,
                'can_read' => $read,
            ]
        );
    }
}
