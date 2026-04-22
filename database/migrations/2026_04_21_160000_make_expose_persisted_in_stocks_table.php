<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stocks')) {
            return;
        }

        if (! Schema::hasColumn('stocks', 'expose')) {
            Schema::table('stocks', function (Blueprint $table): void {
                $table->boolean('expose')->default(false)->after('finition');
            });
            return;
        }

        // Replace generated `expose` with a persisted boolean column.
        DB::statement('ALTER TABLE stocks DROP COLUMN expose');
        Schema::table('stocks', function (Blueprint $table): void {
            $table->boolean('expose')->default(false)->after('finition');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('stocks')) {
            return;
        }

        if (Schema::hasColumn('stocks', 'expose')) {
            DB::statement('ALTER TABLE stocks DROP COLUMN expose');
        }

        Schema::table('stocks', function (Blueprint $table): void {
            $table->tinyInteger('expose')->virtualAs('0')->comment('Calculé automatiquement : toujours 0');
        });
    }
};
