<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration pour créer la table.
     */
    public function up(): void
    {
        Schema::create('cultures', function (Blueprint $table) {
            $table->id();
            $table->string('nom_espece');             // Ex: Maïs, Manioc, Sorgho
            $table->string('variete');                // Ex: TZEE-W SR DC5 (Variété précoce du catalogue CaBEV)
            $table->integer('cycle_vegetatif_jours');  // Durée totale du cycle (Ex: 90 jours)
            $table->float('kc_initial')->default(0.3); // Coefficient cultural de départ (FAO)
            $table->float('kc_milieu')->default(1.2);  // Coefficient au pic de croissance (Période critique)
            $table->float('kc_final')->default(0.6);   // Coefficient en fin de cycle avant récolte
            $table->float('besoin_hydrique_moyen_mm');// Quantité d'eau totale théorique requise
            $table->timestamps();                     // Crée automatiquement les champs created_at et updated_at
        });
    }

    /**
     * Annuler la migration (supprimer la table).
     */
    public function down(): void
    {
        Schema::dropIfExists('cultures');
    }
};