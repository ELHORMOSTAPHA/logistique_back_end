<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes_reservations', function (Blueprint $table) {
            $table->string('vehicle_marque', 100)->nullable()->after('stock_id');
            $table->string('vehicle_modele', 100)->nullable()->after('vehicle_marque');
            $table->string('vehicle_finition', 100)->nullable()->after('vehicle_modele');
            $table->string('vehicle_color_ex', 100)->nullable()->after('vehicle_finition');
            $table->string('vehicle_color_int', 100)->nullable()->after('vehicle_color_ex');
        });
    }

    public function down(): void
    {
        Schema::table('demandes_reservations', function (Blueprint $table) {
            $table->dropColumn(['vehicle_marque', 'vehicle_modele', 'vehicle_finition', 'vehicle_color_ex', 'vehicle_color_int']);
        });
    }
};
