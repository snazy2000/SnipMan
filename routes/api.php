<?php

use App\Http\Controllers\Api\SnippetApiController;
use App\Http\Controllers\Api\TokenController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/snippets', [SnippetApiController::class, 'index']);
    Route::post('/snippets', [SnippetApiController::class, 'store']);
    Route::get('/snippets/{snippet}', [SnippetApiController::class, 'show']);
    Route::get('/folders', [SnippetApiController::class, 'folders']);
    Route::get('/teams', [SnippetApiController::class, 'teams']);
    Route::delete('/tokens/current', [TokenController::class, 'destroy']);
});
