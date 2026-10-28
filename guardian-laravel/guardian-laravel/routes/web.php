<?php

use App\Http\Controllers\AskController;
use App\Http\Controllers\CheckController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/cek');

Route::get('/cek', [PageController::class, 'cek'])->name('cek');
Route::get('/belajar', [PageController::class, 'belajar'])->name('belajar');
Route::get('/bertindak', [PageController::class, 'bertindak'])->name('bertindak');
Route::get('/tanya', [PageController::class, 'tanya'])->name('tanya');

Route::post('/api/cek', [CheckController::class, 'store'])->name('api.cek');
Route::post('/api/tanya', [AskController::class, 'store'])->name('api.tanya');
