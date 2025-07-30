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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::resource('nota-dinas', NotaDinasController::class);
    Route::get('/nota/lampiran/{id}', [NotaDinasController::class, 'getLampiran']);
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
    Route::get('/skpd/{skpd}/nota-induk', [NotaGutulsController::class, 'index'])->name('nota-induk.index');
    Route::resource('nota-skpd', NotaSkpdController::class);

    Route::post('/skpds/{skpd}/programs', [ProgramController::class, 'store'])->name('programs.store');
    Route::put('/programs/{program}', [ProgramController::class, 'update'])->name('programs.update');
    Route::delete('/programs/{program}', [ProgramController::class, 'destroy'])->name('programs.destroy');

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users', [RegisteredUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/toggle-status', [RegisteredUserController::class, 'toggleStatus'])->name('users.toggle-status');

        Route::resource('skpds', SkpdController::class);
        Route::patch('skpds/{skpd}/toggle-status', [SkpdController::class, 'toggleStatus'])->name('skpds.toggle-status');
        Route::get('/skpds/{skpd}/rekap-nota', [SkpdController::class, 'showRekap'])->name('skpds.rekap-nota');
        Route::post('/kabupaten/{kabupaten}/copy-from-previous', [KabupatenController::class, 'copyFromPrevious'])
            ->name('kabupaten.copyFromPrevious');
        // Define API routes for SkpdController
        Route::prefix('skpds/{skpd}/api')->controller(SkpdController::class)->group(function () {
            Route::get('/rekap-nota-per-jenis', 'apiRekapNotaPerJenis')->name('skpds.api.rekap-nota-per-jenis');
            Route::get('/tren-nota-per-bulan', 'apiTrenNotaPerBulan')->name('skpds.api.tren-nota-per-bulan');
            Route::get('/distribusi-sub-kegiatan', 'apiDistribusiSubKegiatan')->name('skpds.api.distribusi-sub-kegiatan');
        });

        Route::resource('kabupaten', KabupatenController::class);

    });
    // API routes for the dashboard
    Route::prefix('api')->controller(DashboardController::class)->group(function () {
        Route::get('/nota-per-year', 'getNotaPerYear');
        Route::get('/skpd/top-serapan', 'topSkpdSerapan');
        Route::get('/kabupaten-serapan', 'getKabupatenSerapanData');
        Route::get('/rekap-nota', 'getRekapNotaByJenis')->name('api.rekap-nota');
    });
});

require __DIR__ . '/auth.php';
