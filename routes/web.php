<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
Route::post('/transactions/{id}', [TransactionController::class, 'update'])->whereNumber('id')->name('transactions.update');
Route::post('/transactions/{id}/delete', [TransactionController::class, 'destroy'])->whereNumber('id')->name('transactions.destroy');

Route::get('/goals', [GoalController::class, 'index'])->name('goals.index');
Route::post('/goals/overall', [GoalController::class, 'saveOverall'])->name('goals.overall');
Route::post('/goals/category', [GoalController::class, 'saveCategory'])->name('goals.category');

Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
Route::post('/profile/name', [ProfileController::class, 'updateName'])->name('profile.name');
Route::post('/accounts', [ProfileController::class, 'storeAccount'])->name('accounts.store');
Route::post('/accounts/{id}', [ProfileController::class, 'updateAccount'])->whereNumber('id')->name('accounts.update');
Route::post('/accounts/{id}/delete', [ProfileController::class, 'destroyAccount'])->whereNumber('id')->name('accounts.destroy');
Route::post('/categories', [ProfileController::class, 'storeCategory'])->name('categories.store');
Route::post('/categories/delete', [ProfileController::class, 'destroyCategory'])->name('categories.destroy');

Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/auth/signup', [AuthController::class, 'signup'])->name('auth.signup');
Route::post('/auth/google', [AuthController::class, 'google'])->name('auth.google');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');