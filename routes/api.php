<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TrackController;
use App\Http\Middleware\IsAdminMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/tracks', [TrackController::class, 'index']);
Route::get('/last_update', [TrackController::class, 'lastUpdate']);
Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group( function () {
    Route::middleware(IsAdminMiddleware::class)->group( function () {
        Route::get('/update', [TrackController::class, 'updateTracks']);
        Route::post('/add_track', [TrackController::class, 'addTrack']);
    });
    Route::get('/user', function (Request $request) {
        return$request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
});
