<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Admin auth routes - views only (Fortify handles POST actions at /manage/*)
Route::prefix('manage')->group(function () {
    // Guest routes
    Route::middleware('guest')->group(function () {
        Route::get('login', fn () => Inertia::render('auth/Login'))
            ->name('login');

        Route::get('register', fn () => Inertia::render('auth/Register'))
            ->name('register');

        Route::get('forgot-password', fn () => Inertia::render('auth/ForgotPassword'))
            ->name('password.request');

        Route::get('reset-password/{token}', fn ($token) => Inertia::render('auth/ResetPassword', ['token' => $token]))
            ->name('password.reset');
    });

    // Auth routes
    Route::middleware('auth')->group(function () {
        Route::get('two-factor-challenge', fn () => Inertia::render('auth/TwoFactorChallenge'))
            ->name('two-factor.login');

        Route::get('confirm-password', fn () => Inertia::render('auth/ConfirmPassword'))
            ->name('password.confirm');
    });
});
