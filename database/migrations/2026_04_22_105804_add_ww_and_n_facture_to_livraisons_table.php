<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('livraisons', function (Blueprint $table) {
            if (!Schema::hasColumn('livraisons', 'ww')) {
                $table->string('ww', 50)->nullable()->comment('Numéro WW livraison')->after('statut');
            }
            if (!Schema::hasColumn('livraisons', 'n_facture')) {
                $table->string('n_facture', 100)->nullable()->comment('Numéro de facture')->after('ww');
            }
        });
    }

    public function down(): void
    {
        Schema::table('livraisons', function (Blueprint $table) {
            $table->dropColumn(['ww', 'n_facture']);
        });
    }
};
