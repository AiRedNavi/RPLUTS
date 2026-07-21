<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\PortManagementController;
use App\Http\Controllers\Admin\ArticleManagementController;
use App\Http\Controllers\Admin\NewsSyncController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\CurrencySyncController;

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

Route::post('/currency/sync', [CurrencySyncController::class, 'run'])
    ->middleware('auth')
    ->name('currency.sync');
// ------------------------------------------------------------------
// Admin
// ------------------------------------------------------------------
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('index');

    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/toggle-role', [UserManagementController::class, 'toggleRole'])->name('users.toggle-role');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

    Route::get('/ports', [PortManagementController::class, 'index'])->name('ports.index');
    Route::post('/ports', [PortManagementController::class, 'store'])->name('ports.store');
    Route::put('/ports/{port}', [PortManagementController::class, 'update'])->name('ports.update');
    Route::delete('/ports/{port}', [PortManagementController::class, 'destroy'])->name('ports.destroy');

    Route::get('/articles', [ArticleManagementController::class, 'index'])->name('articles.index');
    Route::post('/articles', [ArticleManagementController::class, 'store'])->name('articles.store');
    Route::put('/articles/{article}', [ArticleManagementController::class, 'update'])->name('articles.update');
    Route::delete('/articles/{article}', [ArticleManagementController::class, 'destroy'])->name('articles.destroy');

    Route::post('/news/sync', [NewsSyncController::class, 'run'])->name('news.sync');
});