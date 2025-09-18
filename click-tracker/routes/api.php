<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClickCtaController;

Route::get('/ping', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API online 🚀'
    ]);
});

Route::post('/clicks-cta', [ClickCtaController::class, 'store']);

// FALLBACK - deve ser a última rota
Route::fallback(function () {
    return response()->json([
        'error' => 'Endpoint not found',
        'message' => 'O endpoint da API que você está procurando não existe',
        'status' => 404,
       
    ], 404);
});