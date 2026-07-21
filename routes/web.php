<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserManagementController;

Route::get('/', [DashboardController::class, 'index']);
Route::get('/weather', [DashboardController::class, 'weather']);
Route::get('/currency', [DashboardController::class, 'currency']);
Route::get('/news', [DashboardController::class, 'news']);
Route::get('/ports', [DashboardController::class, 'ports']);
Route::get('/comparison', [DashboardController::class, 'comparison']);

Route::get('/watchlist', [DashboardController::class, 'watchlist'])->middleware('auth');

// ------------------------------------------------------------------
// Auth
// ------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('auth.logout');

// ------------------------------------------------------------------
// Admin
// ------------------------------------------------------------------
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/', '/admin/users');
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/toggle-role', [UserManagementController::class, 'toggleRole'])->name('users.toggle-role');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
});