<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CreditCardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecurringRuleController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response('ok', 200)->header('Content-Type', 'text/plain');
})->name('health');

Route::get('/up', function () {
    return response('ok', 200)->header('Content-Type', 'text/plain');
})->name('up');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->name('login.perform')->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('accounts', AccountController::class)->except(['show']);
    Route::resource('credit-cards', CreditCardController::class)->except(['show', 'destroy']);
    Route::resource('categories', CategoryController::class)->except(['show', 'destroy']);
    Route::resource('recurring-rules', RecurringRuleController::class)->except(['show', 'destroy']);

    Route::post('/schedule/generate', [ScheduleController::class, 'generate'])->name('schedule.generate');
});
