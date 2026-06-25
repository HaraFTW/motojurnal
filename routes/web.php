<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CombustibilController;
use App\Http\Controllers\DistanceUnitController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\UleiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::patch('/distance-unit', [DistanceUnitController::class, 'update'])->name('distance-unit.update');
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    Route::get('/combustibil', [CombustibilController::class, 'index'])->name('fuel.index');
    Route::post('/combustibil', [CombustibilController::class, 'store'])->name('fuel.store');
    Route::put('/combustibil/{combustibil}', [CombustibilController::class, 'update'])->name('fuel.update');
    Route::delete('/combustibil/{combustibil}', [CombustibilController::class, 'destroy'])->name('fuel.destroy');
    Route::get('/ulei', [UleiController::class, 'index'])->name('oil.index');
    Route::post('/ulei', [UleiController::class, 'store'])->name('oil.store');
    Route::put('/ulei/{ulei}', [UleiController::class, 'update'])->name('oil.update');
    Route::delete('/ulei/{ulei}', [UleiController::class, 'destroy'])->name('oil.destroy');
    Route::get('/evenimente', [EventController::class, 'index'])->name('events.index');
    Route::post('/evenimente', [EventController::class, 'store'])->name('events.store');
    Route::put('/evenimente/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/evenimente/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
