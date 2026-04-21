<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Backward compatibility: if old table exists, rename it.
        if (Schema::hasTable('stock_statuses') && !Schema::hasTable('stock_statuts')) {
            Schema::rename('stock_statuses', 'stock_statuts');
        }

        Schema::table('stock_statuts', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_statuts', 'libelle')) {
                $table->string('libelle', 45)->nullable()->after('id');
            }
            if (!Schema::hasColumn('stock_statuts', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('stock_statuts', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        // Backfill `libelle` from legacy `name` when available.
        if (Schema::hasColumn('stock_statuts', 'name')) {
            DB::table('stock_statuts')
                ->whereNull('libelle')
                ->update(['libelle' => DB::raw('name')]);
        }

        Schema::table('stock_statuts', function (Blueprint $table) {
            if (Schema::hasColumn('stock_statuts', 'stock_status')) {
                $table->dropColumn('stock_status');
            }
            if (Schema::hasColumn('stock_statuts', 'name')) {
                $table->dropColumn('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_statuts', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_statuts', 'name')) {
                $table->string('name', 45)->nullable();
            }
            if (!Schema::hasColumn('stock_statuts', 'stock_status')) {
                $table->string('stock_status', 45)->nullable();
            }
            if (Schema::hasColumn('stock_statuts', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
            if (Schema::hasColumn('stock_statuts', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('stock_statuts', 'libelle')) {
                $table->dropColumn('libelle');
            }
        });

        if (Schema::hasTable('stock_statuts') && !Schema::hasTable('stock_statuses')) {
            Schema::rename('stock_statuts', 'stock_statuses');
        }
    }
};

