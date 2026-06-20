<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Gérer l'inscription d'un nouvel agriculteur.
     * * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // 1. Validation stricte des données entrantes
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'telephone' => 'required|string',
        ]);

        // 2. Création de l'utilisateur avec hachage du mot de passe (Bcrypt)
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telephone' => $request->telephone,
            'role' => 'agriculteur', // Rôle imposé par défaut pour l'inscription mobile
        ]);

        // 3. Ouverture et stockage manuel des variables dans la session PHP
        $request->session()->put('user_id', $user->id);
        $request->session()->put('user_role', $user->role);
        $request->session()->put('user_nom', $user->prenom . ' ' . $user->nom);

        // 4. Régénération de l'ID de session pour bloquer la fixation de session
        $request->session()->regenerate();

        // 5. Réponse au format JSON avec statut 201 (Created)
        return response()->json([
            'status' => 'success',
            'message' => 'Compte agricole créé avec succès.',
            'user' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'email' => $user->email,
                'role' => $user->role,
                'telephone' => $user->telephone
            ],
            'redirect' => '/dashboard'
        ], 201);
    }

    /**
     * Gérer l'authentification et l'ouverture de la session (Connexion).
     * * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // 1. Validation des données entrantes envoyées par l'application mobile
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Recherche de l'utilisateur dans la base de données via son email unique
        $user = User::where('email', $request->email)->first();

        // 3. Vérification de la correspondance du mot de passe haché (Bcrypt)
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Identifiants ou mot de passe incorrects.'
            ], 401);
        }

        // 4. Création et stockage manuel des variables dans la session PHP de Laravel
        $request->session()->put('user_id', $user->id);
        $request->session()->put('user_role', $user->role); 
        $request->session()->put('user_nom', $user->prenom . ' ' . $user->nom);

        // 5. Régénération de l'ID de session pour bloquer les attaques par fixation de session
        $request->session()->regenerate();

        // 6. Envoi de la réponse de succès avec les informations essentielles de profil
        return response()->json([
            'status' => 'success',
            'message' => 'Authentification réussie avec succès.',
            'user' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'email' => $user->email,
                'role' => $user->role,
                'telephone' => $user->telephone
            ],
            'redirect' => $user->role === 'admin' ? '/admin/dashboard' : '/dashboard'
        ], 200);
    }

    /**
     * Gérer la fermeture de la session et la déconnexion sécurisée.
     * * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // 1. Suppression ciblée des clés stockées en session
        $request->session()->forget(['user_id', 'user_role', 'user_nom']);

        // 2. Destruction complète de la session courante sur le serveur
        $request->session()->invalidate();

        // 3. Régénération du jeton CSRF de session pour des raisons de sécurité
        $request->session()->regenerateToken();

        // 4. Retour du succès de l'opération à l'application mobile
        return response()->json([
            'status' => 'success',
            'message' => 'Déconnexion effectuée. Session détruite proprement.'
        ], 200);
    }
}