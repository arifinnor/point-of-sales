<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // User management routes with specific permission middleware
    Route::get('users', [UserController::class, 'index'])
        ->middleware('can:view_user')
        ->name('users.index');

    Route::get('users/create', [UserController::class, 'create'])
        ->middleware('can:manage_user')
        ->name('users.create');

    Route::post('users', [UserController::class, 'store'])
        ->middleware('can:manage_user')
        ->name('users.store');

    Route::get('users/{user}', [UserController::class, 'show'])
        ->middleware('can:view_user')
        ->name('users.show');

    Route::get('users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('can:manage_user')
        ->name('users.edit');

    Route::put('users/{user}', [UserController::class, 'update'])
        ->middleware('can:manage_user')
        ->name('users.update');

    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->middleware('can:manage_user')
        ->name('users.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
