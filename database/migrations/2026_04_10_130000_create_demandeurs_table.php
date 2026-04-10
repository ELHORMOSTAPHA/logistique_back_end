<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandeurs', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 45)->nullable();
            $table->string('id_demandeur', 45)->nullable()->unique()->comment('Identifiant métier du demandeur');
            $table->string('site_demandeur', 45)->nullable();
            $table->string('profile', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandeurs');
    }
};
