<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stocks')->cascadeOnDelete();
            $table->string('id_demande', 45)->nullable()->comment('via commercial');
            $table->string('nom_commercial', 45)->nullable();
            $table->integer('id_commercial')->nullable();
            $table->string('demande_infos', 45)->nullable();
            $table->string('statut', 45)->nullable()->default('en cours');
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_reservations');
    }
};
