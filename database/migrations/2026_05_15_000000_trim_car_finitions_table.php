<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Colonnes conservées : id, name, modele_id (+ index modele_id). */
    private const DROP_COLUMNS = [
        'energie',
        'cv',
        'transmission',
        'bv',
        'pd',
        'emission_co2',
        'categorie',
        'carrosserie',
        'nbr_place',
        'nbr_porte',
        'prix',
        'prix_circulaire_particulier',
        'prix_circulaire_autre',
        'prix_circulaire_lcd',
        'prix_circulaire_lld',
        'prix_circulaire_pgm',
        'remise_vendeur',
        'remise_vendeur_type',
        'remise_chef_vente',
        'remise_chef_vente_type',
        'remise_directeur_marque',
        'remise_directeur_marque_type',
        'remise_directeur_globale',
        'remise_directeur_globale_type',
        'remise_vendeur_particulier',
        'remise_vendeur_particulier_type',
        'remise_vendeur_societe',
        'remise_vendeur_societe_type',
        'remise_vendeur_lcd',
        'remise_vendeur_lcd_type',
        'remise_vendeur_lld',
        'remise_vendeur_lld_type',
        'remise_vendeur_pgm',
        'remise_vendeur_pgm_type',
        'remise_chef_vente_particulier',
        'remise_chef_vente_particulier_type',
        'remise_chef_vente_societe',
        'remise_chef_vente_societe_type',
        'remise_chef_vente_lcd',
        'remise_chef_vente_lcd_type',
        'remise_chef_vente_lld',
        'remise_chef_vente_lld_type',
        'remise_chef_vente_pgm',
        'remise_chef_vente_pgm_type',
        'remise_directeur_marque_particulier',
        'remise_directeur_marque_particulier_type',
        'remise_directeur_marque_societe',
        'remise_directeur_marque_societe_type',
        'remise_directeur_marque_lcd',
        'remise_directeur_marque_lcd_type',
        'remise_directeur_marque_lld',
        'remise_directeur_marque_lld_type',
        'remise_directeur_marque_pgm',
        'remise_directeur_marque_pgm_type',
        'remise_directeur_globale_particulier',
        'remise_directeur_globale_particulier_type',
        'remise_directeur_globale_societe',
        'remise_directeur_globale_societe_type',
        'remise_directeur_globale_lcd',
        'remise_directeur_globale_lcd_type',
        'remise_directeur_globale_lld',
        'remise_directeur_globale_lld_type',
        'remise_directeur_globale_pgm',
        'remise_directeur_globale_pgm_type',
    ];

    public function up(): void
    {
        $toDrop = array_values(array_filter(
            self::DROP_COLUMNS,
            static fn (string $column): bool => Schema::hasColumn('car_finitions', $column),
        ));

        if ($toDrop === []) {
            return;
        }

        Schema::table('car_finitions', function (Blueprint $table) use ($toDrop) {
            $table->dropColumn($toDrop);
        });
    }

    public function down(): void
    {
        Schema::table('car_finitions', function (Blueprint $table) {
            $table->string('energie', 50);
            $table->string('cv', 10);
            $table->string('transmission', 50);
            $table->string('bv', 50);
            $table->string('pd', 10);
            $table->string('emission_co2', 25);
            $table->string('categorie', 30);
            $table->string('carrosserie', 30);
            $table->integer('nbr_place');
            $table->integer('nbr_porte');
            $table->decimal('prix', 10, 2)->default(0);
            $table->decimal('prix_circulaire_particulier', 10, 2);
            $table->decimal('prix_circulaire_autre', 10, 2);
            $table->decimal('prix_circulaire_lcd', 10, 2);
            $table->decimal('prix_circulaire_lld', 10, 2);
            $table->decimal('prix_circulaire_pgm', 10, 2);
            $table->decimal('remise_vendeur', 10, 2)->default(0);
            $table->enum('remise_vendeur_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_chef_vente', 10, 2)->default(0);
            $table->enum('remise_chef_vente_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_directeur_marque', 10, 2)->default(0);
            $table->enum('remise_directeur_marque_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_directeur_globale', 10, 2)->default(0);
            $table->enum('remise_directeur_globale_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_vendeur_particulier', 10, 2)->default(0);
            $table->enum('remise_vendeur_particulier_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_vendeur_societe', 10, 2)->default(0);
            $table->enum('remise_vendeur_societe_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_vendeur_lcd', 10, 2)->default(0);
            $table->enum('remise_vendeur_lcd_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_vendeur_lld', 10, 2)->default(0);
            $table->enum('remise_vendeur_lld_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_vendeur_pgm', 10, 2)->default(0);
            $table->enum('remise_vendeur_pgm_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_chef_vente_particulier', 10, 2)->default(0);
            $table->enum('remise_chef_vente_particulier_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_chef_vente_societe', 10, 2)->default(0);
            $table->enum('remise_chef_vente_societe_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_chef_vente_lcd', 10, 2)->default(0);
            $table->enum('remise_chef_vente_lcd_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_chef_vente_lld', 10, 2)->default(0);
            $table->enum('remise_chef_vente_lld_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_chef_vente_pgm', 10, 2)->default(0);
            $table->enum('remise_chef_vente_pgm_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_directeur_marque_particulier', 10, 2)->default(0);
            $table->enum('remise_directeur_marque_particulier_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_directeur_marque_societe', 10, 2)->default(0);
            $table->enum('remise_directeur_marque_societe_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_directeur_marque_lcd', 10, 2)->default(0);
            $table->enum('remise_directeur_marque_lcd_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_directeur_marque_lld', 10, 2)->default(0);
            $table->enum('remise_directeur_marque_lld_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_directeur_marque_pgm', 10, 2)->default(0);
            $table->enum('remise_directeur_marque_pgm_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_directeur_globale_particulier', 10, 2)->default(0);
            $table->enum('remise_directeur_globale_particulier_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_directeur_globale_societe', 10, 2)->default(0);
            $table->enum('remise_directeur_globale_societe_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_directeur_globale_lcd', 10, 2)->default(0);
            $table->enum('remise_directeur_globale_lcd_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_directeur_globale_lld', 10, 2)->default(0);
            $table->enum('remise_directeur_globale_lld_type', ['percent', 'amount'])->default('percent');
            $table->decimal('remise_directeur_globale_pgm', 10, 2)->default(0);
            $table->enum('remise_directeur_globale_pgm_type', ['percent', 'amount'])->default('percent');
        });
    }
};
