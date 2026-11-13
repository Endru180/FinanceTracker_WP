<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::controller(TransactionController::class)
    ->prefix('transactions')
    ->name('transactions.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::post('/{id}', 'update')->whereNumber('id')->name('update');
        Route::post('/{id}/delete', 'destroy')->whereNumber('id')->name('destroy');
    });

Route::controller(GoalController::class)
    ->prefix('goals')
    ->name('goals.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/overall', 'saveOverall')->name('overall');
        Route::post('/category', 'saveCategory')->name('category');
    });

Route::controller(ProfileController::class)->group(function () {
    Route::get('/profile', 'show')->name('profile');
    Route::post('/profile/name', 'updateName')->name('profile.name');

    Route::post('/accounts', 'storeAccount')->name('accounts.store');
    Route::post('/accounts/{id}', 'updateAccount')->whereNumber('id')->name('accounts.update');
    Route::post('/accounts/{id}/delete', 'destroyAccount')->whereNumber('id')->name('accounts.destroy');

    Route::post('/categories', 'storeCategory')->name('categories.store');
    Route::post('/categories/delete', 'destroyCategory')->name('categories.destroy');
});

Route::controller(AuthController::class)
    ->prefix('auth')
    ->name('auth.')
    ->group(function () {
        Route::post('/login', 'login')->name('login');
        Route::post('/signup', 'signup')->name('signup');
        Route::post('/google', 'google')->name('google');
        Route::post('/logout', 'logout')->name('logout');
    });
