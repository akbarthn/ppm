<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KaryawanController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\ScheadulesController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfilController;

// Tampilkan form login (GET /login)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');

// Aksi login (POST /login)
Route::post('/login', [AuthController::class, 'login']);

// Logout (POST /logout)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Redirect dari / ke login
Route::get('/', function () {
    return redirect()->route('login');
});

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
    Route::get('/absensi/scan', [AbsensiController::class, 'scan'])->name('absensi.scan');
    Route::post('/absensi/check-status', [AbsensiController::class, 'checkStatus'])->name('absensi.checkStatus');
    Route::post('/absensi/checkin', [AbsensiController::class, 'checkin']);
    Route::post('/absensi/checkout', [AbsensiController::class, 'checkout']);
    Route::post('/absensi/keterangan', [AbsensiController::class, 'setKeterangan'])->name('absensi.keterangan');
    Route::get('/reports/attendance', [ReportController::class, 'attendanceReport'])->name('reports.attendance');
    Route::get('/profil', [ProfilController::class, 'index'])->name('profil.index');
    Route::get('/profil/edit', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::post('/profil/update', [ProfilController::class, 'update'])->name('profil.update');

    });