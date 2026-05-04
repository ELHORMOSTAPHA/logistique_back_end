<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('livraisons', function (Blueprint $table) {
            $table->string('type_client', 100)->nullable()->after('crm_cmd_id');
            $table->string('nom_commercial', 255)->nullable()->after('type_client');
            $table->string('nom_succursale', 255)->nullable()->after('nom_commercial');
        });
    }

    public function down(): void
    {
        Schema::table('livraisons', function (Blueprint $table) {
            $table->dropColumn(['type_client', 'nom_commercial', 'nom_succursale']);
        });
    }
};
