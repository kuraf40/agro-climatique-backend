<?php

namespace database\seeders;

use Illuminate\Database\Seeder;
use App\Models\Culture;

class CultureSeeder extends Seeder
{
    /**
     * Exécuter l'injection de données de référence.
     */
    public function run(): void
    {
        // Insertion de la variété de Maïs précoce de 90 jours (Données Fiche Technique INRAB / CaBEV Bénin)
        Culture::create([
            'nom_espece' => 'Maïs',
            'variete' => 'TZEE-W SR DC5',
            'cycle_vegetatif_jours' => 90,
            'kc_initial' => 0.40,
            'kc_milieu' => 1.15, // Période d'exigence hydrique maximale (Phase critique)
            'kc_final' => 0.70,
            'besoin_hydrique_moyen_mm' => 500.0 // Besoins moyens pour un cycle complet en zone soudanienne
        ]);

        // Insertion d'une autre variété courante au Bénin pour diversifier le catalogue
        Culture::create([
            'nom_espece' => 'Sorgho',
            'variete' => 'Choroni local',
            'cycle_vegetatif_jours' => 120,
            'kc_initial' => 0.35,
            'kc_milieu' => 1.10,
            'kc_final' => 0.65,
            'besoin_hydrique_moyen_mm' => 650.0
        ]);
    }
}