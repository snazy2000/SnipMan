<?php

use App\Http\Controllers\FolderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SnippetController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('snippets.index');
});

Route::get('/dashboard', function () {
    return redirect()->route('snippets.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Resource routes for snippet management
    Route::resource('teams', TeamController::class);
    Route::resource('folders', FolderController::class);
    Route::resource('snippets', SnippetController::class);
});

require __DIR__.'/auth.php';
