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
        Schema::table('demandes_reservations', function (Blueprint $table) {
            $table->date('date_commande')->nullable()->after('id_demande');
            $table->date('date_livraison')->nullable()->after('date_commande');
            $table->decimal('net_a_payer', 12, 2)->nullable()->after('date_livraison');
        });
    }

    public function down(): void
    {
        Schema::table('demandes_reservations', function (Blueprint $table) {
            $table->dropColumn(['date_commande', 'date_livraison', 'net_a_payer']);
        });
    }
};
