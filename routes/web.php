<?php

use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileBirthChartController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileHoroscopeController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatThreadController;
use App\Http\Controllers\HoroscopeController;
use App\Http\Controllers\HoroscopeToolsController;
use App\Http\Controllers\Admin\AdminConversationController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminVisitorController;
use Illuminate\Support\Facades\Route;

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('horoscope.index');
    }

    return view('welcome');
});

Route::get('/dashboard', function () {
    return redirect()->route('horoscope.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/horoscope', [ProfileHoroscopeController::class, 'edit'])->name('profile.horoscope.edit');
    Route::patch('/profile/horoscope', [ProfileHoroscopeController::class, 'update'])->name('profile.horoscope.update');

    Route::get('/profile/birth-charts', [ProfileBirthChartController::class, 'index'])->name('profile.birth-charts.index');
    Route::get('/profile/birth-charts/create', [ProfileBirthChartController::class, 'create'])->name('profile.birth-charts.create');
    Route::post('/profile/birth-charts', [ProfileBirthChartController::class, 'store'])->name('profile.birth-charts.store');
    Route::get('/profile/birth-charts/{birthChart}/edit', [ProfileBirthChartController::class, 'edit'])->name('profile.birth-charts.edit');
    Route::patch('/profile/birth-charts/{birthChart}', [ProfileBirthChartController::class, 'update'])->name('profile.birth-charts.update');
    Route::delete('/profile/birth-charts/{birthChart}', [ProfileBirthChartController::class, 'destroy'])->name('profile.birth-charts.destroy');

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/models', [ChatController::class, 'models'])->name('chat.models');
    Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');

    Route::get('/chat/threads', [ChatThreadController::class, 'index'])->name('chat.threads.index');
    Route::post('/chat/threads', [ChatThreadController::class, 'store'])->name('chat.threads.store');
    Route::get('/chat/threads/{thread}', [ChatThreadController::class, 'show'])->name('chat.threads.show');

    Route::get('/horoscope', [HoroscopeController::class, 'index'])->name('horoscope.index');
    Route::get('/api/geocode', [HoroscopeController::class, 'geocode'])->name('horoscope.geocode');
    Route::post('/api/horoscope/calc', [HoroscopeController::class, 'calculate'])->name('horoscope.calculate');
    Route::post('/api/horoscope/chat', [HoroscopeController::class, 'chat'])->name('horoscope.chat');

    Route::post('/api/tools/transit/now', [HoroscopeToolsController::class, 'transitNow'])->name('tools.transit.now');
    Route::post('/api/tools/transit/find', [HoroscopeToolsController::class, 'findEvent'])->name('tools.transit.find');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/visitors', [AdminVisitorController::class, 'index'])->name('visitors.index');
    Route::patch('/visitors/{visitor}/ban', [AdminVisitorController::class, 'ban'])->name('visitors.ban');
    Route::patch('/visitors/{visitor}/unban', [AdminVisitorController::class, 'unban'])->name('visitors.unban');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');

    Route::get('/conversations', [AdminConversationController::class, 'index'])->name('conversations.index');
});

require __DIR__.'/auth.php';
