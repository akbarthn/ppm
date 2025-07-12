<?php
use App\Http\Controllers\AuthController;

// Form login
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
// Aksi login
Route::post('/login', [AuthController::class, 'login']);
// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard redirect (hanya contoh)
Route::get('/dashboard', function () {
    return view('dashboard.index');
})->middleware('auth');

