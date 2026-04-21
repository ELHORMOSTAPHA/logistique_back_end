<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('depots', function (Blueprint $table) {
            $table->foreignId('type_depot_id')
                ->nullable()
                ->after('type')
                ->constrained('type_depots')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('depots', function (Blueprint $table) {
            $table->dropForeign(['type_depot_id']);
            $table->dropColumn('type_depot_id');
        });
    }
};
