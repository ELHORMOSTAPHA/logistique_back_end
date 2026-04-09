<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->string('numero_lot', 45)->nullable();
            $table->string('numero_arrivage', 45)->nullable();
            $table->string('statut', 45)->nullable()->comment('1. En fabrication (usine) | 2. En cours d\'acheminement | 3. Arrivé au port | 4. Entrée en stock (réception)');
            $table->date('date_arrivage_prevu')->nullable();
            $table->string('created_by', 45)->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lots');
    }
};
