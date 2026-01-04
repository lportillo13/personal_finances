<?php

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response('ok', 200)->header('Content-Type', 'text/plain');
})->name('health');

Route::redirect('/', '/dashboard');

Route::view('/dashboard', 'dashboard')->name('dashboard');
