<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Création du compte de l'Administrateur (Gestionnaire des cultures et seuils)
        User::create([
            'nom' => 'Gandonou',
            'prenom' => 'Jean',
            'email' => 'admin@agroclimatique.bj',
            'password' => Hash::make('Admin2026*'), // Mot de passe haché de manière sécurisée
            'role' => 'admin',
            'telephone' => '+22997000001'
        ]);

        // 2. Création du compte d'un Agriculteur de test (pour Banikoara)
        User::create([
            'nom' => 'Sabi',
            'prenom' => 'Orou',
            'email' => 'orou.sabi@agri.bj',
            'password' => Hash::make('Agri2026*'),
            'role' => 'agriculteur',
            'telephone' => '+22996000002'
        ]);
    }
}