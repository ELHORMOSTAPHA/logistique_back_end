<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_modeles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->foreignId('marque_id')->constrained('car_marques');
            $table->integer('status')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_modeles');
    }
};
