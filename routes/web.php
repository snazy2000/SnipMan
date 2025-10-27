<?php

use App\Http\Controllers\FolderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SnippetController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('snippets.index');
});

Route::get('/dashboard', function () {
    return redirect()->route('snippets.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// Public route for shared snippets (no authentication required)
Route::get('/s/{uuid}', [SnippetController::class, 'viewShared'])->name('snippets.shared');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Sharing routes (must be before resource routes to avoid conflicts)
    Route::get('/snippets/shared-list', [SnippetController::class, 'sharedList'])->name('snippets.sharedList');
    Route::get('/snippets/{snippet}/share-status', [SnippetController::class, 'getShareStatus'])->name('snippets.shareStatus');
    Route::post('/snippets/{snippet}/share', [SnippetController::class, 'createShare'])->name('snippets.createShare');
    Route::post('/snippets/{snippet}/toggle-share', [SnippetController::class, 'toggleShare'])->name('snippets.toggleShare');
    Route::delete('/snippets/{snippet}/revoke-share', [SnippetController::class, 'revokeShare'])->name('snippets.revokeShare');

    // AI processing routes
    Route::post('/snippets/{snippet}/process-ai', [SnippetController::class, 'processAI'])->name('snippets.processAI');

    // Search routes
    Route::get('/search', [SearchController::class, 'search'])->name('search');

    // Drag and drop routes
    Route::post('/snippets/{snippet}/move', [SnippetController::class, 'move'])->name('snippets.move');
    Route::post('/folders/{folder}/move', [FolderController::class, 'move'])->name('folders.move');

    // Resource routes for snippet management
    Route::resource('teams', TeamController::class);

    // Team member management routes
    Route::post('/teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.addMember');
    Route::patch('/teams/{team}/members/{user}', [TeamController::class, 'updateMemberRole'])->name('teams.updateMemberRole');
    Route::delete('/teams/{team}/members/{user}', [TeamController::class, 'removeMember'])->name('teams.removeMember');

    Route::resource('folders', FolderController::class);
    Route::resource('snippets', SnippetController::class);
});

require __DIR__.'/auth.php';
