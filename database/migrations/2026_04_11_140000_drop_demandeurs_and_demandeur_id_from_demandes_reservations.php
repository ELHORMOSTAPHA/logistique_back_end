<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('demandes_reservations') && Schema::hasColumn('demandes_reservations', 'demandeur_id')) {
            Schema::table('demandes_reservations', function (Blueprint $table) {
                $table->dropForeign(['demandeur_id']);
            });
            Schema::table('demandes_reservations', function (Blueprint $table) {
                $table->dropColumn('demandeur_id');
            });
        }

        Schema::dropIfExists('demandeurs');
    }

    public function down(): void
    {
        Schema::create('demandeurs', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 45)->nullable();
            $table->string('id_demandeur', 45)->nullable()->unique();
            $table->string('site_demandeur', 45)->nullable();
            $table->string('profile', 45)->nullable();
            $table->timestamps();
        });

        Schema::table('demandes_reservations', function (Blueprint $table) {
            $table->foreignId('demandeur_id')
                ->nullable()
                ->after('stock_id')
                ->constrained('demandeurs')
                ->nullOnDelete();
        });
    }
};
