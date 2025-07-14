<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KaryawanController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\ScheadulesController;
use App\Http\Controllers\AbsensiController;


// Form login
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
// Aksi login
Route::post('/login', [AuthController::class, 'login']);
// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard redirect (hanya contoh)
Route::get('/dashboard', function () {
    return view('dashboard.index');
})->middleware('auth')->name('dashboard.index');

Route::middleware('auth')->group(function () {
    Route::resource('karyawan', KaryawanController::class);
    Route::resource('shift', ShiftController::class);
    Route::resource('jadwal', ScheadulesController::class);
    Route::delete('/jadwal-group', [ScheadulesController::class, 'destroyGroup'])->name('jadwal.destroyGroup');
    Route::get('/jadwal-group/edit', [ScheadulesController::class, 'editGroup'])->name('jadwal.editGroup');
    Route::put('/jadwal-group/update', [ScheadulesController::class, 'updateGroup'])->name('jadwal.updateGroup');
    Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
Route::get('/absensi/{shift}/scan', [AbsensiController::class, 'scan'])->name('absensi.scan');
Route::post('/absensi/checkin', [AbsensiController::class, 'checkin'])->name('absensi.checkin');
Route::post('/absensi/checkout', [AbsensiController::class, 'checkout'])->name('absensi.checkout');
Route::post('/absensi/keterangan', [AbsensiController::class, 'setKeterangan'])->name('absensi.keterangan');
    });