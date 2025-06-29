<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KabupatenController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\{NotaDinasController, NotaGutulsController, NotaSkpdController, ProgramController};
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
    Route::get('/api/rekap-nota', [DashboardController::class, 'getRekapNotaByJenis'])->name('api.rekap-nota');
    Route::post('/skpds/{skpd}/kegiatan', [KegiatanController::class, 'store'])->name('kegiatans.store');
    Route::put('/kegiatans/{kegiatan}', [KegiatanController::class, 'update'])->name('kegiatans.update');
    Route::delete('/kegiatans/{kegiatan}', [KegiatanController::class, 'destroy'])->name('kegiatans.destroy');
    Route::post('/kegiatan/{kegiatan}/sub', [SubKegiatanController::class, 'store'])->name('subkegiatans.store');
    Route::resource('kegiatans.subkegiatans', SubKegiatanController::class)->only(['update', 'destroy']);
    Route::get('/nota-dinas/sub-kegiatan/{id}', [SubKegiatanController::class, 'show'])->name('sub-kegiatan.nota-dinas');
    Route::get('/skpds/{skpd}/tahun/{tahun?}', [SkpdController::class, 'showByYear'])->name('skpds.tahun');
    Route::get('/skpds/{skpd}/nota-gutuls', [NotaGutulsController::class, 'notaGuTuLsBySkpd'])->name('nota-dinas.nota-gutuls');
    Route::post('/store-gutuls', [NotaGutulsController::class, 'storeGuTuLs'])->name('store-gutuls');
    Route::put('/update-gutuls/{notaDina}', [NotaGutulsController::class, 'updateGuTuLs'])->name('update-gutuls');
    Route::resource('nota-skpd', NotaSkpdController::class);

    Route::post('/skpds/{skpd}/programs', [ProgramController::class, 'store'])->name('programs.store');
    Route::put('/programs/{program}', [ProgramController::class, 'update'])->name('programs.update');
    Route::delete('/programs/{program}', [ProgramController::class, 'destroy'])->name('programs.destroy');

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
        Route::get('/skpds/{skpd}/rekap-nota', [SkpdController::class, 'showRekap'])->name('skpds.rekap-nota');
        Route::get('/skpds/{skpd}/api/rekap-nota-per-jenis', [SkpdController::class, 'apiRekapNotaPerJenis'])
        ->name('skpds.api.rekap-nota-per-jenis');
        Route::get('/skpds/{skpd}/api/tren-nota-per-bulan', [SkpdController::class, 'apiTrenNotaPerBulan'])
        ->name('skpds.api.tren-nota-per-bulan');
        Route::get('/skpds/{skpd}/api/distribusi-sub-kegiatan', [SkpdController::class, 'apiDistribusiSubKegiatan'])
        ->name('skpds.api.distribusi-sub-kegiatan');

        Route::resource('kabupaten', KabupatenController::class);

    });
    
});




require __DIR__.'/auth.php';
