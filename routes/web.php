<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrinoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/query', function () {
    return view('query');
});

Route::get('/drift', function () {
    return view('drift-detection');
});

Route::get('/api', function () {
    return response()->json([
        'message' => 'Laravel Trino Data Governance API',
        'version' => '1.0.0',
        'endpoints' => [
            // Trino Query Endpoints
            'GET /trino/test' => 'Test Trino connection',
            'POST /trino/query' => 'Execute a Trino query (synchronous)',
            'GET /trino/users' => 'Get users from MySQL via Trino',
            'GET /trino/products' => 'Get products from MySQL via Trino',
            
            // Database Drift Detection
            'POST /drift/detect-schema' => 'Detect schema differences between databases',
            'POST /drift/detect-rowcount' => 'Detect row count differences',
            'POST /drift/detect-tables' => 'Find missing tables',
            'POST /drift/report' => 'Get comprehensive drift report',
            'POST /drift/snapshot' => 'Capture schema snapshot',
            'POST /drift/compare/{id}' => 'Compare with snapshot',
        ]
    ]);
});

Route::prefix('trino')->group(function () {
    Route::get('/test', [TrinoController::class, 'test']);
    Route::post('/query', [TrinoController::class, 'executeQuery']);
    Route::get('/users', [TrinoController::class, 'getUsers']);
    Route::get('/products', [TrinoController::class, 'getProducts']);
});

// Database Drift Detection routes
Route::prefix('drift')->group(function () {
    Route::post('/detect-schema', [\App\Http\Controllers\DriftDetectionController::class, 'detectSchemaDrift']);
    Route::post('/detect-rowcount', [\App\Http\Controllers\DriftDetectionController::class, 'detectRowCountDrift']);
    Route::post('/detect-tables', [\App\Http\Controllers\DriftDetectionController::class, 'detectMissingTables']);
    Route::post('/report', [\App\Http\Controllers\DriftDetectionController::class, 'getDriftReport']);
    Route::post('/snapshot', [\App\Http\Controllers\DriftDetectionController::class, 'captureSnapshot']);
    Route::get('/snapshots', [\App\Http\Controllers\DriftDetectionController::class, 'listSnapshots']);
    Route::post('/compare/{snapshotId}', [\App\Http\Controllers\DriftDetectionController::class, 'compareWithSnapshot']);
});

