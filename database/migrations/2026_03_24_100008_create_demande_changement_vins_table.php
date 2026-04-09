<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_changement_vins', function (Blueprint $table) {
            $table->id();
            $table->integer('demandeur')->nullable();
            $table->string('valideur', 45)->nullable();
            $table->string('motif', 45)->nullable();
            $table->string('vin_remplace', 45)->nullable();
            $table->string('statut', 45)->nullable();
            $table->foreignId('demandes_reservation_id')->constrained('demandes_reservations')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_changement_vins');
    }
};
