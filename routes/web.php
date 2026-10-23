<?php

use App\Http\Controllers\AdminCertificateController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminLessonController;
use App\Http\Controllers\AdminModuleController;
use App\Http\Controllers\AdminSettingController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LearningController::class, 'landing'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('register.store');

    // Masuk / daftar dengan Google
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

    // Lupa kata sandi (nama route "password.*" dipakai oleh Laravel saat membuat tautan di email)
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1')->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Verifikasi email (hanya perlu login; belum perlu terverifikasi)
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])->middleware('throttle:6,1')->name('verification.send');
});

// Area peserta & admin: wajib login DAN email sudah terverifikasi
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [LearningController::class, 'dashboard'])->name('dashboard');

    // Profil & pengaturan akun
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->middleware('throttle:10,1')->name('profile.password.update');
    Route::get('/learning/{module}/{lesson?}', [LearningController::class, 'show'])->name('learning.show');
    Route::post('/learning/{lesson}/finish', [ProgressController::class, 'finish'])->middleware('throttle:60,1')->name('learning.finish');
    Route::post('/learning/{lesson}/quiz', [ProgressController::class, 'quiz'])->name('learning.quiz');

    Route::post('/certificate', [CertificateController::class, 'store'])->name('certificate.store');
    Route::get('/certificate/download', [CertificateController::class, 'download'])->name('certificate.download');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::get('/modules', [AdminModuleController::class, 'index'])->name('modules.index');
        Route::get('/modules/create', [AdminModuleController::class, 'create'])->name('modules.create');
        Route::post('/modules', [AdminModuleController::class, 'store'])->name('modules.store');
        Route::get('/modules/{module}/edit', [AdminModuleController::class, 'edit'])->name('modules.edit');
        Route::put('/modules/{module}', [AdminModuleController::class, 'update'])->name('modules.update');
        Route::delete('/modules/{module}', [AdminModuleController::class, 'destroy'])->name('modules.destroy');

        Route::get('/lessons', [AdminLessonController::class, 'index'])->name('lessons.index');
        Route::get('/lessons/create', [AdminLessonController::class, 'create'])->name('lessons.create');
        Route::post('/lessons', [AdminLessonController::class, 'store'])->name('lessons.store');
        Route::post('/lessons/upload-image', [AdminLessonController::class, 'uploadImage'])->name('lessons.upload-image');
        Route::get('/lessons/{lesson}/edit', [AdminLessonController::class, 'edit'])->name('lessons.edit');
        Route::put('/lessons/{lesson}', [AdminLessonController::class, 'update'])->name('lessons.update');
        Route::delete('/lessons/{lesson}', [AdminLessonController::class, 'destroy'])->name('lessons.destroy');

        Route::get('/settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/partners', [AdminSettingController::class, 'storePartner'])->name('settings.partners.store');
        Route::delete('/settings/partners/{partner}', [AdminSettingController::class, 'destroyPartner'])->name('settings.partners.destroy');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/import/template', [AdminUserController::class, 'downloadImportTemplate'])->name('users.import.template');
        Route::post('/users/import', [AdminUserController::class, 'import'])->name('users.import');
        Route::post('/users/reset-all-learning', [AdminUserController::class, 'resetAllLearning'])->name('users.reset-all-learning');
        Route::post('/users/{user}/reset-learning', [AdminUserController::class, 'resetLearning'])->name('users.reset-learning');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        Route::get('/certificate', [AdminCertificateController::class, 'edit'])->name('certificate.edit');
        Route::get('/certificate/preview', [AdminCertificateController::class, 'preview'])->name('certificate.preview');
        Route::post('/certificate/{user}/approve', [AdminCertificateController::class, 'approve'])->name('certificate.approve');
        Route::post('/certificate/background', [AdminCertificateController::class, 'uploadBackground'])->name('certificate.background');
        Route::post('/certificate/number', [AdminCertificateController::class, 'saveNumber'])->name('certificate.number');
        Route::post('/certificate/fields', [AdminCertificateController::class, 'saveFields'])->name('certificate.fields');
        Route::post('/certificate/fields/add', [AdminCertificateController::class, 'addCustomField'])->name('certificate.fields.add');
        Route::delete('/certificate/fields/{key}', [AdminCertificateController::class, 'removeField'])->name('certificate.fields.remove');
    });
});
