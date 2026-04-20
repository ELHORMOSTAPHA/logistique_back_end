<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_modification_vins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demandes_reservation_id')->constrained('demandes_reservations')->cascadeOnDelete();
            $table->foreignId('stock_id')->constrained('stocks')->cascadeOnDelete();
            $table->foreignId('demandeur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('vin_nouveau', 45)->nullable();
            $table->string('motif')->nullable();
            $table->string('statut', 45)->default('en_attente')->comment('en_attente | approuvée | refusée');
            $table->foreignId('valideur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->text('motif_refus')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_modification_vins');
    }
};
