<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('modele', 45)->nullable();
            $table->string('version', 45)->nullable();
            $table->string('marque', 45)->nullable();
            $table->string('vin', 45)->nullable()->unique();
            $table->tinyInteger('expose')->virtualAs('0')->comment('Calculé automatiquement : toujours 0');
            $table->string('color_ex', 45)->nullable();
            $table->string('color_ex_code', 45)->nullable();
            $table->string('color_int', 45)->nullable();
            $table->string('color_int_code', 45)->nullable();
            $table->boolean('reserved')->nullable()->default(false);
            $table->foreignId('depot_id')->nullable()->constrained('depots')->nullOnDelete();
            $table->foreignId('lot_id')->constrained('lots')->cascadeOnDelete();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
