<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AgroMeteoController; // <-- Très important : il faut importer le contrôleur ici !


Route::post('/api/login', [AuthController::class, 'login']);
Route::post('/api/logout', [AuthController::class, 'logout']);


Route::middleware([\App\Http\Middleware\EnsureUserIsLoggedIn::class])->group(function () {
    

    Route::get('/api/dashboard', [AgroMeteoController::class, 'getDashboardData']);
    
});