<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_finitions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->unsignedBigInteger('modele_id');

            $table->index('modele_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_finitions');
    }
};
