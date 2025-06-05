<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KabupatenController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\NotaDinasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\SkpdController;
use App\Http\Controllers\SubKegiatanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return inertia('Auth/Login', [
        'canResetPassword' => Route::has('/'),
    ]);
})->middleware('guest')->name('/');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::resource('nota-dinas', NotaDinasController::class);
    Route::get('/nota/lampiran/{id}', [NotaDinasController::class, 'getLampiran']);
    Route::get('api/nota-per-year', [DashboardController::class, 'getNotaPerYear']);
    Route::get('api/skpd/top-serapan', [DashboardController::class, 'topSkpdSerapan']);
    Route::get('/api/kabupaten-serapan', [DashboardController::class, 'getKabupatenSerapanData']);
    Route::post('/skpds/{skpd}/kegiatan', [KegiatanController::class, 'store'])->name('kegiatans.store');
    Route::put('/kegiatans/{kegiatan}', [KegiatanController::class, 'update'])->name('kegiatans.update');
    Route::delete('/kegiatans/{kegiatan}', [KegiatanController::class, 'destroy'])->name('kegiatans.destroy');
    Route::post('/kegiatan/{kegiatan}/sub', [SubKegiatanController::class, 'store'])->name('subkegiatans.store');
    Route::resource('kegiatans.subkegiatans', SubKegiatanController::class)->only(['update', 'destroy']);
    Route::get('/nota-dinas/sub-kegiatan/{id}', [SubKegiatanController::class, 'show'])->name('sub-kegiatan.nota-dinas');
    Route::get('/skpds/{skpd}/tahun/{tahun?}', [SkpdController::class, 'showByYear'])->name('skpds.tahun');
    Route::get('/skpds/{skpd}/nota-gutuls', [NotaDinasController::class, 'notaGuTuLsBySkpd'])->name('nota-dinas.nota-gutuls');
    Route::get('/nota-dinas/create-gutuls/{skpd}', [NotaDinasController::class, 'createGuTuLs'])->name('nota-dinas.create-gutuls');
    Route::post('/store-gutuls', [NotaDinasController::class, 'storeGuTuLs'])->name('store-gutuls');

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
