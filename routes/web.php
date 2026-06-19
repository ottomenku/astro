<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\HoroscopeController;
use App\Http\Controllers\Admin\AdminConversationController;
use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/models', [ChatController::class, 'models'])->name('chat.models');
    Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');

    Route::get('/horoscope', [HoroscopeController::class, 'index'])->name('horoscope.index');
    Route::get('/api/geocode', [HoroscopeController::class, 'geocode'])->name('horoscope.geocode');
    Route::post('/api/horoscope/calc', [HoroscopeController::class, 'calculate'])->name('horoscope.calculate');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');

    Route::get('/conversations', [AdminConversationController::class, 'index'])->name('conversations.index');
});

require __DIR__.'/auth.php';
