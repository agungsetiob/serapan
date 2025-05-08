<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KabupatenController;
use App\Http\Controllers\NotaDinasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\SkpdController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Auth/Login', [
        'canResetPassword' => Route::has('/'),
    ]);
})->middleware('guest')->name('/');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/nota/lampiran/{tipe}/{id}', [NotaDinasController::class, 'getLampiranHistori']);
    Route::resource('nota-dinas', NotaDinasController::class);
    Route::get('/nota/lampiran/{id}', [NotaDinasController::class, 'getLampiran']);
    Route::get('/nota-per-year', [DashboardController::class, 'getNotaPerYear']);
    Route::get('/approved-nota-dinas', [DashboardController::class, 'getApprovedNotaDinasBySkpd']);
    Route::get('/nota-dinas-stage', [DashboardController::class, 'getNotaDinasByStage']);

});

Route::middleware(['auth'])->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users', [RegisteredUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/toggle-status', [RegisteredUserController::class, 'toggleStatus'])->name('users.toggle-status');
        
        Route::resource('skpds', SkpdController::class);
        Route::patch('skpds/{skpd}/toggle-status', [SkpdController::class, 'toggleStatus'])->name('skpds.toggle-status');

        Route::resource('kabupaten', KabupatenController::class);

    });
    
});




require __DIR__.'/auth.php';
