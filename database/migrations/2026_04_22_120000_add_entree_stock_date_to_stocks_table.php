<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stocks')) {
            return;
        }

        Schema::table('stocks', function (Blueprint $table): void {
            if (! Schema::hasColumn('stocks', 'entree_stock_date')) {
                $table->dateTime('entree_stock_date')->nullable()->after('stock_status_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('stocks') || ! Schema::hasColumn('stocks', 'entree_stock_date')) {
            return;
        }

        Schema::table('stocks', function (Blueprint $table): void {
            $table->dropColumn('entree_stock_date');
        });
    }
};
