<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Heure de l’affectation dépôt (aligné avec l’UI « date + heure »).
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE depot_historiques MODIFY created_at DATETIME NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE depot_historiques MODIFY created_at DATE NULL');
    }
};
