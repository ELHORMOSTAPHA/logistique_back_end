<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_statuts', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_statuts', 'is_available_for_update')) {
                $table->boolean('is_available_for_update')->default(true)->after('libelle');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_statuts', function (Blueprint $table) {
            if (Schema::hasColumn('stock_statuts', 'is_available_for_update')) {
                $table->dropColumn('is_available_for_update');
            }
        });
    }
};
