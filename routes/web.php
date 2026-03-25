<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ChatController;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('chat')
        : redirect()->route('login');
});

Route::get('/login', function () {
    return view('login');
})->name('login')->middleware('guest');

Route::get('/chat', [ChatController::class, 'index'])
    ->name('chat')
    ->middleware('auth');

Route::get('/messages/{id}', [ChatController::class, 'messages'])
    ->middleware('auth');

Route::get('/users/search', [ChatController::class, 'searchUsers'])
    ->middleware('auth');

Route::post('/send', [ChatController::class, 'send'])
    ->middleware('auth');

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])
    ->name('google.redirect');

Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])
    ->name('google.callback');

Route::post('/logout', [GoogleController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');