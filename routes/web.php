<?php

use App\Http\Controllers\OutletController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TenantController;
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
    // Tenant switching route
    Route::post('tenant/switch', [TenantController::class, 'switch'])
        ->name('tenant.switch');

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

    // Outlet management routes
    Route::get('outlets', [OutletController::class, 'index'])
        ->middleware('can:view_outlet')
        ->name('outlets.index');

    Route::get('outlets/create', [OutletController::class, 'create'])
        ->middleware('can:manage_outlet')
        ->name('outlets.create');

    Route::post('outlets', [OutletController::class, 'store'])
        ->middleware('can:manage_outlet')
        ->name('outlets.store');

    Route::get('outlets/{outlet}/edit', [OutletController::class, 'edit'])
        ->middleware('can:manage_outlet')
        ->name('outlets.edit');

    Route::put('outlets/{outlet}', [OutletController::class, 'update'])
        ->middleware('can:manage_outlet')
        ->name('outlets.update');

    Route::delete('outlets/{outlet}', [OutletController::class, 'destroy'])
        ->middleware('can:manage_outlet')
        ->name('outlets.destroy');

    // Register management routes
    Route::get('registers', [RegisterController::class, 'index'])
        ->middleware('can:view_register')
        ->name('registers.index');

    Route::get('registers/create', [RegisterController::class, 'create'])
        ->middleware('can:manage_register')
        ->name('registers.create');

    Route::post('registers', [RegisterController::class, 'store'])
        ->middleware('can:manage_register')
        ->name('registers.store');

    Route::get('registers/{register}/edit', [RegisterController::class, 'edit'])
        ->middleware('can:manage_register')
        ->name('registers.edit');

    Route::put('registers/{register}', [RegisterController::class, 'update'])
        ->middleware('can:manage_register')
        ->name('registers.update');

    Route::delete('registers/{register}', [RegisterController::class, 'destroy'])
        ->middleware('can:manage_register')
        ->name('registers.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
