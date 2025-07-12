<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KaryawanController;
use Illuminate\Support\Facades\Route;

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
});