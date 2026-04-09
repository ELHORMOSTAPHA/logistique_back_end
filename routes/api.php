<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DemandeReservationController;
use App\Http\Controllers\Api\DepotController;
use App\Http\Controllers\Api\HistoriqueController;
use App\Http\Controllers\Api\LotController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\UtilisateurController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - MoLogistic
|--------------------------------------------------------------------------
*/

// Routes publiques (sans authentification)
Route::prefix('auth')->group(function () {
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/login',   [AuthController::class, 'login']);
});

// Routes protégées (JWT)
Route::middleware('jwt.auth')->group(function () {
    // Auth
    Route::get('/auth/user', [AuthController::class, 'userDetails']);
    Route::get('/auth/me',   [AuthController::class, 'me']);

    // Stocks — CRUD: GET/POST /api/stocks, GET/PUT/PATCH/DELETE /api/stocks/{stock}
    // Route::apiResource('stock', StockController::class);
    // Route::patch('stocks/{id}/depot', [StockController::class, 'changeDepot'])->whereNumber('id');
    Route::apiResource('stock', StockController::class);
    //import excel
    Route::post('stock/import-stock', [StockController::class, 'importStock']);
    //depot
    Route::apiResource('depot', DepotController::class);
    //lot
    Route::apiResource('lot', LotController::class);
    //historique
    Route::apiResource('historique', HistoriqueController::class);
    //demande_reservation
    Route::apiResource('demande_reservation', DemandeReservationController::class);
    //utilisateur
    Route::apiResource('utilisateur', UtilisateurController::class);
    //profiles
    Route::apiResource('profile', ProfileController::class);
  
});
