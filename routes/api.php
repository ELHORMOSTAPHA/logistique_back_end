<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExternalSyncController;
use App\Http\Controllers\Api\DemandeReservationController;
use App\Http\Controllers\Api\DepotController;
use App\Http\Controllers\Api\HistoriqueController;
use App\Http\Controllers\Api\IntegrationAuthController;
use App\Http\Controllers\Api\LotController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\StockStatusController;
use App\Http\Controllers\Api\UtilisateurController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - MoLogistic
|--------------------------------------------------------------------------
*/


// Serve public storage files (bypasses php artisan serve symlink limitation)
Route::get('/files/{path}', function (string $path) {
    $fullPath = storage_path('app/public/' . $path);
    abort_if(! file_exists($fullPath), 404);
    return response()->file($fullPath);
})->where('path', '.*');

// Routes publiques (sans authentification)
Route::prefix('auth')->group(function () {
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/login',   [AuthController::class, 'login']);
    Route::post('/integration/token', [IntegrationAuthController::class, 'token']);
});

// Routes protégées (JWT)
Route::middleware('jwt.auth')->group(function () {
    // Auth
    Route::get('/auth/user', [AuthController::class, 'userDetails']);
    Route::get('/auth/me',   [AuthController::class, 'me']);
    // Stocks — CRUD: GET/POST /api/stocks, GET/PUT/PATCH/DELETE /api/stocks/{stock}
    // Route::apiResource('stock', StockController::class);
    // Route::patch('stocks/{id}/depot', [StockController::class, 'changeDepot'])->whereNumber('id');
    Route::get('stock/{stock}/depot-historique', [StockController::class, 'depotHistorique'])
        ->whereNumber('stock');
    Route::get('stock-statuses', [StockStatusController::class, 'index']);
    Route::apiResource('stock', StockController::class);
    // Import JSON rows (client parses .xlsx / .csv / .ods)
    Route::post('stock/import-stock', [StockController::class, 'importStock']);
    Route::post('stock/import', [StockController::class, 'importStock']);
    Route::post('stock/preview-vin-update', [StockController::class, 'previewVinUpdate']);
    Route::post('stock/bulk-assign-lot', [StockController::class, 'bulkAssignLot']);
    Route::post('stock/bulk-change-depot', [StockController::class, 'bulkChangeDepot']);
    //depot
    Route::apiResource('depot', DepotController::class);
    //historique
    Route::apiResource('historique', HistoriqueController::class);
    //demande_reservation
    Route::apiResource('demande_reservation', DemandeReservationController::class);
    Route::get('demande_reservation/{demande_reservation}/matching-stock',  [DemandeReservationController::class, 'matchingStock']);
    Route::post('demande_reservation/{demande_reservation}/affecter-vin',   [DemandeReservationController::class, 'affecterVin']);
    Route::get('demande_reservation/{demande_reservation}/matching-vin',    [DemandeReservationController::class, 'matchingVin']);
    Route::post('demande_reservation/{demande_reservation}/modifier-vin',   [DemandeReservationController::class, 'modifierVin']);
    //utilisateur — routes dédiées avant apiResource (sinon "bulk-update-status" est pris pour un id)
    Route::post('utilisateur/bulk-update-status', [UtilisateurController::class, 'bulkUpdateStatus']);
    Route::apiResource('utilisateur', UtilisateurController::class);
    //profiles — specific routes before apiResource
    Route::post('profile/bulk-update-status', [ProfileController::class, 'bulkUpdateStatus']);
    Route::get('profile/{profile}/permissions', [ProfileController::class, 'permissions']);
    Route::put('profile/{profile}/permissions', [ProfileController::class, 'updatePermissions']);
    Route::apiResource('profile', ProfileController::class);

});
//to get brearer token lance this commande first
#  php artisan integration:client:create "crm_exeedd" --scopes=integration.test --ttl=3600
//then make request to this url http://localhost:8000/api/integration/token with the following headers:
//Authorization: Bearer <INTEGRATION_API_TOKEN>
//Content-Type: application/json
//{
//    "client_id": "crm_exeedd",
//    "client_secret": "crm_exeedd"
//}
//then you will get the brearer token
//you should refresh token after 24 h
// Routes for external integrations (system-to-system)
Route::prefix('integration')->middleware('integration.auth')->group(function () {
    Route::post('/sync-commande', [ExternalSyncController::class, 'syncCommande']);
    Route::get('/test', function (Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'Integration authentication success.',
            'data' => [
                'client' => [
                    'id' => optional($request->attributes->get('integration_client'))->id,
                    'client_id' => optional($request->attributes->get('integration_client'))->client_id,
                ],
                'client_ip' => $request->ip(),
                'timestamp' => \Carbon\Carbon::now()->toISOString(),
            ],
        ]);
    });
    //stock
    Route::prefix('stock')->group(function () {
        //list stock aproximit
        Route::get('/', [StockController::class, 'listStockAproximit']);
        // recherche ancien VIN (ou placeholder sans VIN) par identité véhicule
        Route::get('/old-vin', [StockController::class, 'getOldVinInStock']);
    });
});
