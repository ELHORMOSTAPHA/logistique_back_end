<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('stocks', 'marque')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->string('marque', 45)->nullable()->after('finition');
            });
        }
        //remove verison column
        if (Schema::hasColumn('stocks', 'version')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->dropColumn('version');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('stocks', 'marque')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->dropColumn('marque');
            });
        }
    }
};
