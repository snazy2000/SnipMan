<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AISettingsController;
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

// Public route for shared snippets (no authentication required, rate limited)
Route::get('/s/{uuid}', [SnippetController::class, 'viewShared'])
    ->name('snippets.shared')
    ->middleware('throttle:100,1');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.theme.update');
    Route::patch('/profile/language', [ProfileController::class, 'updateLanguage'])->name('profile.language.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Sharing routes (must be before resource routes to avoid conflicts)
    Route::get('/snippets/shared-list', [SnippetController::class, 'sharedList'])->name('snippets.sharedList');
    Route::get('/snippets/{snippet}/share-status', [SnippetController::class, 'getShareStatus'])->name('snippets.shareStatus');
    Route::post('/snippets/{snippet}/share', [SnippetController::class, 'createShare'])->name('snippets.createShare');
    Route::post('/snippets/{snippet}/toggle-share', [SnippetController::class, 'toggleShare'])->name('snippets.toggleShare');
    Route::delete('/snippets/{snippet}/revoke-share', [SnippetController::class, 'revokeShare'])->name('snippets.revokeShare');

    // AI processing routes (rate limit to prevent abuse of AI resources)
    Route::post('/snippets/{snippet}/process-ai', [SnippetController::class, 'processAI'])
        ->name('snippets.processAI')
        ->middleware('throttle:10,1');

    // Clone/Fork snippet route
    Route::post('/snippets/{snippet}/clone', [SnippetController::class, 'clone'])->name('snippets.clone');

    // Search routes (with rate limiting to prevent abuse)
    Route::get('/search', [SearchController::class, 'search'])->name('search')->middleware('throttle:60,1');

    // Drag and drop routes
    Route::post('/snippets/{snippet}/move', [SnippetController::class, 'move'])->name('snippets.move');
    Route::post('/folders/{folder}/move', [FolderController::class, 'move'])->name('folders.move');

    // Resource routes for snippet management
    Route::resource('teams', TeamController::class);

    // Team member management routes
    Route::post('/teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.addMember');
    Route::patch('/teams/{team}/members/{user}', [TeamController::class, 'updateMemberRole'])->name('teams.updateMemberRole');
    Route::delete('/teams/{team}/members/{user}', [TeamController::class, 'removeMember'])->name('teams.removeMember');
    Route::post('/teams/{team}/members/{user}/resend', [TeamController::class, 'resendInvitation'])->name('teams.resendInvitation');

    Route::resource('folders', FolderController::class);
    Route::resource('snippets', SnippetController::class);
});

// Admin routes - only accessible by super admins
Route::middleware(['auth', 'super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');

    // User management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::patch('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::post('/users/{user}/resend', [AdminController::class, 'resendUserInvitation'])->name('users.resendInvitation');

    // Team management
    Route::get('/teams', [AdminController::class, 'teams'])->name('teams');
    Route::get('/teams/{team}', [AdminController::class, 'showTeam'])->name('teams.show');
    Route::get('/teams/{team}/edit', [AdminController::class, 'editTeam'])->name('teams.edit');
    Route::patch('/teams/{team}', [AdminController::class, 'updateTeam'])->name('teams.update');
    Route::delete('/teams/{team}', [AdminController::class, 'destroyTeam'])->name('teams.destroy');

    // AI Settings routes (moved to admin)
    Route::get('/ai/settings', [AISettingsController::class, 'index'])->name('ai.settings');
    Route::post('/ai/settings', [AISettingsController::class, 'update'])->name('ai.settings.update');
    Route::post('/ai/reset', [AISettingsController::class, 'resetToDefaults'])->name('ai.settings.reset');
    Route::post('/ai/clear-caches', [AISettingsController::class, 'clearCaches'])->name('ai.settings.clear-caches');
});

require __DIR__.'/auth.php';
