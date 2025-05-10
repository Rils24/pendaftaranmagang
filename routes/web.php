<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PendaftaranController;


// Halaman utama (home)
Route::get('/', function () {
    return view('home');
})->name('home');

// Halaman login dan register
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Verifikasi OTP
Route::get('/otp-verify', [RegisterController::class, 'showOtpVerificationForm'])->name('otp.verify.form');
Route::post('/otp-verify', [RegisterController::class, 'verifyOtp'])->name('otp.verify');
Route::get('/otp/resend', [RegisterController::class, 'resendOtp'])->name('otp.resend');

// Rute untuk user yang sudah login
Route::middleware(['auth'])->group(function () {
    // Halaman pendaftaran user - PERBAIKAN: Gunakan controller yang sama
    Route::get('/userpendaftaran', [PendaftaranController::class, 'create'])->name('user.pendaftaran');

    // Form pendaftaran
    Route::get('/pendaftaran', [PendaftaranController::class, 'create'])->name('pendaftaran');
    Route::post('/pendaftaran', [PendaftaranController::class, 'store'])->name('form.pendaftaran');

    // Status pendaftaran
    Route::get('/status-pendaftaran', [PendaftaranController::class, 'status'])->name('status.pendaftaran');

    // Batalkan pendaftaran
    Route::get('/batalkan-pendaftaran/{id}', [PendaftaranController::class, 'batalkanPendaftaran'])
        ->name('batalkan.pendaftaran');

    // Cetak bukti pendaftaran
    Route::get('/cetak-bukti/{id}', [PendaftaranController::class, 'cetakBukti'])
        ->name('cetak.bukti');

    // Hapus pendaftaran yang ditolak dan redirect ke form pendaftaran baru
    Route::get('/hapus-pendaftaran/{id}', [PendaftaranController::class, 'hapusPendaftaran'])
        ->name('hapus.pendaftaran');

    // Tambahkan route untuk print pendaftaran
    Route::get('/pendaftaran/print/{id}', [PendaftaranController::class, 'print'])
        ->name('pendaftaran.print');

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Rute untuk admin
Route::middleware(['auth', 'admin'])->group(function () {
    // Tidak perlu definisikan apa-apa, Filament sudah mengatur route admin
});

// Route untuk Settings di Filament
Route::middleware(['auth', 'admin'])
    ->prefix('admin/settings')
    ->name('filament.admin.resources.settings.')
    ->group(function () {
        Route::get('/', function () {
            $setting = \App\Models\Setting::firstOrCreate([]);
            return redirect()->route('filament.admin.resources.settings.index');
        })->name('index');
    });
// View PDF
Route::get('/view-pdf/{encryptedId}', [PendaftaranController::class, 'viewPdf'])
    ->name('view.pdf')
    ->middleware('auth');