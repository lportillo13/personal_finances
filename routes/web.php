<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response('ok', 200)->header('Content-Type', 'text/plain');
})->name('health');

Route::get('/up', function () {
    return response('ok', 200)->header('Content-Type', 'text/plain');
})->name('up');

Route::redirect('/', '/dashboard');

Route::get('/dashboard', function () {
    return response('Personal Finance System - Setup Complete', 200)->header('Content-Type', 'text/plain');
})->name('dashboard');
