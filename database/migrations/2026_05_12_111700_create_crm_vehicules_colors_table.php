<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_vehicules_colors', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->string('reference', 50);
            $table->decimal('prix', 10, 2)->default(0);
            $table->unsignedBigInteger('modele_id');
            $table->enum('type', ['ext', 'int'])->nullable();
            $table->string('hex_color', 10);

            $table->index('modele_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_vehicules_colors');
    }
};
