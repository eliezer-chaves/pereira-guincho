<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClickCtaController;


Route::get('/ping', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API online 🚀'
    ]);
});

Route::post('/create-session', [ClickCtaController::class, 'createSession']);
Route::post('/clicks-cta', [ClickCtaController::class, 'store']);
Route::post('/clicks-cta-timer', [ClickCtaController::class, 'storeTimer']);

// FALLBACK - deve ser a última rota
Route::fallback(function () {
    return response()->json([
        'error' => 'Endpoint not found',
        'message' => 'O endpoint da API que você está procurando não existe',
        'status' => 404,
    ], 404);
});