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
            if (! Schema::hasColumn('stocks', 'expose_date')) {
                $table->dateTime('expose_date')->nullable()->after('expose');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('stocks') || ! Schema::hasColumn('stocks', 'expose_date')) {
            return;
        }

        Schema::table('stocks', function (Blueprint $table): void {
            $table->dropColumn('expose_date');
        });
    }
};
