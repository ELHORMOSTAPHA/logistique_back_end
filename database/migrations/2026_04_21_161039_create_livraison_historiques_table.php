<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('livraison_historiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('livraison_id')->constrained('livraisons')->cascadeOnDelete();
            $table->string('statut', 50)->comment('en_attente, facturé, livré');
            $table->string('infos', 255)->nullable()->comment('N° facture ou WW livraison');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livraison_historiques');
    }
};
