<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_motifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demandes_reservation_id')
                ->constrained('demandes_reservations')
                ->cascadeOnDelete();
            $table->string('motifs_description', 45)->nullable();
            $table->string('file_path', 255)->nullable();
            $table->string('file_type', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_motifs');
    }
};
