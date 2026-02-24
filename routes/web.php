<?php


use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'getLogin'])->name('parent.login');
});


Route::middleware(['web', 'parent.auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('parent.dashboard');

    Route::get('/select-childs', [DashboardController::class, 'selectChilds'])
        ->name('parent.select-childs');



    Route::get('/profile', [DashboardController::class, 'getProfile'])
        ->name('parent.profile');

    Route::get('/notifications', [DashboardController::class, 'getNotifications'])
        ->name('parent.notifications');
});
