<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demande_modification_vins', function (Blueprint $table) {
            // VIN that was assigned to the demande BEFORE the change request
            $table->string('vin_initial', 45)->nullable()->after('stock_id');
        });
    }

    public function down(): void
    {
        Schema::table('demande_modification_vins', function (Blueprint $table) {
            $table->dropColumn('vin_initial');
        });
    }
};
