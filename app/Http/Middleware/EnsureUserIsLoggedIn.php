<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsLoggedIn
{
    /**
     * Intercepter la requête entrante pour vérifier la session.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si la variable 'user_id' n'existe pas en session, l'utilisateur n'est pas connecté
        if (!$request->session()->has('user_id')) {
            return response()->json([
                'status' => 'unauthorized',
                'message' => 'Accès refusé. Veuillez vous connecter.'
            ], 401);
        }

        // Si l'utilisateur est connecté, on laisse passer la requête vers le contrôleur suivant
        return $next($request);
    }
}