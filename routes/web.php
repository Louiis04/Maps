<?php

use App\Http\Controllers\API\MapController;
use App\Http\Controllers\MapViewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [MapViewController::class, 'index'])->name('maps.index');
Route::get('/maps/new', [MapViewController::class, 'create'])->name('maps.create'); 
Route::get('/maps/{id}/view', [MapViewController::class, 'show'])->name('maps.show');
Route::get('/maps/{id}/edit', [MapViewController::class, 'edit'])->name('maps.edit');
Route::post('/maps/save', [MapViewController::class, 'store'])->name('maps.store');
Route::put('/maps/{id}', [MapViewController::class, 'update'])->name('maps.update');

// API routes
Route::withoutMiddleware(['web', 'csrf'])->prefix('api')->group(function () {
    Route::get('/maps', [MapController::class, 'index']);
    Route::post('/maps', [MapController::class, 'store']);
    Route::get('/maps/{id}', [MapController::class, 'show']);
    Route::delete('/maps/{id}', [MapController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});