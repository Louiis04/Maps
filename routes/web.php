<?php

use App\Http\Controllers\API\MapController;
use App\Http\Controllers\MapViewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::withoutMiddleware(['web', 'csrf'])->group(function () {
    Route::get('/maps', [MapController::class, 'index']);
    Route::post('/maps', [MapController::class, 'store']);
    Route::get('/maps/{id}', [MapController::class, 'show']);
    Route::delete('/maps/{id}', [MapController::class, 'destroy']);
});

Route::get('/', [MapViewController::class, 'index'])->name('maps.index');
Route::get('/maps/{id}/view', [MapViewController::class, 'show'])->name('maps.show');
Route::get('/maps/new', [MapViewController::class, 'create'])->name('maps.create');
Route::post('/maps/save', [MapViewController::class, 'store'])->name('maps.store');