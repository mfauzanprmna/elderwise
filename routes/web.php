<?php

use App\Http\Controllers\AdminCertificateController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminLessonController;
use App\Http\Controllers\AdminModuleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\ProgressController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LearningController::class, 'landing'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [LearningController::class, 'dashboard'])->name('dashboard');
    Route::get('/learning/{module}/{lesson?}', [LearningController::class, 'show'])->name('learning.show');
    Route::post('/learning/{lesson}/complete', [ProgressController::class, 'complete'])->name('learning.complete');
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
        Route::get('/lessons/{lesson}/edit', [AdminLessonController::class, 'edit'])->name('lessons.edit');
        Route::put('/lessons/{lesson}', [AdminLessonController::class, 'update'])->name('lessons.update');
        Route::delete('/lessons/{lesson}', [AdminLessonController::class, 'destroy'])->name('lessons.destroy');

        Route::get('/certificate', [AdminCertificateController::class, 'edit'])->name('certificate.edit');
        Route::get('/certificate/preview', [AdminCertificateController::class, 'preview'])->name('certificate.preview');
        Route::post('/certificate/background', [AdminCertificateController::class, 'uploadBackground'])->name('certificate.background');
        Route::post('/certificate/fields', [AdminCertificateController::class, 'saveFields'])->name('certificate.fields');
        Route::post('/certificate/fields/add', [AdminCertificateController::class, 'addCustomField'])->name('certificate.fields.add');
        Route::delete('/certificate/fields/{key}', [AdminCertificateController::class, 'removeField'])->name('certificate.fields.remove');
    });
});
