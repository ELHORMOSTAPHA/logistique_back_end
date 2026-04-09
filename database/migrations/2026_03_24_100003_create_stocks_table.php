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
            $table->string('numero_commande', 45)->nullable();
            $table->string('client', 120)->nullable();
            $table->string('type_client', 45)->nullable();
            $table->string('PGEO', 45)->nullable();
            $table->string('finition', 45)->nullable();
            $table->tinyInteger('expose')->virtualAs('0')->comment('Calculé automatiquement : toujours 0');
            $table->string('color_ex', 45)->nullable();
            $table->string('color_ex_code', 45)->nullable();
            $table->string('color_int', 45)->nullable();
            $table->string('color_int_code', 45)->nullable();
            $table->text('options')->nullable();
            $table->string('vendeur', 120)->nullable();
            $table->string('site_affecte', 120)->nullable();
            $table->date('date_creation_commande')->nullable();
            $table->boolean('reserved')->nullable()->default(false);
            $table->foreignId('depot_id')->nullable()->constrained('depots')->nullOnDelete();
            $table->foreignId('stock_status_id')->nullable()->constrained('stock_statuses')->nullOnDelete();
            $table->date('date_arrivage_prevu')->nullable();
            $table->date('date_arrivage_reelle')->nullable();
            $table->date('date_affectation')->nullable();
            $table->string('numero_lot', 45)->nullable();
            $table->string('numero_arrivage', 45)->nullable();
            $table->string('statut', 45)->nullable();
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
