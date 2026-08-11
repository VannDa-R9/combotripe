<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\AuthController;

// Core Pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/destination', [ProvinceController::class, 'index'])->name('destination.index');
Route::get('/destination/{id}', [ProvinceController::class, 'show'])->name('destination.show');
Route::get('/place/{id}', [PlaceController::class, 'show'])->name('place.show');
Route::get('/explore', [ExploreController::class, 'index'])->name('explore');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/api/live-search', [SearchController::class, 'liveSearch'])->name('api.liveSearch');


// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/signup', [AuthController::class, 'showSignup'])->name('signup');
    Route::post('/signup', [AuthController::class, 'signup']);

    // Google OAuth
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/place/{id}/review', [PlaceController::class, 'storeReview'])->middleware('verified')->name('reviews.store');

    // Email Verification Routes
    Route::get('/email/verify', [AuthController::class, 'showVerificationNotice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])->middleware('throttle:6,1')->name('verification.send');
});

// Admin routes group
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('provinces', App\Http\Controllers\Admin\ProvinceController::class)->except(['show']);
    Route::resource('places', App\Http\Controllers\Admin\PlaceController::class)->except(['show']);
    Route::resource('places.foods', App\Http\Controllers\Admin\PlaceFoodController::class);
    Route::resource('places.activities', App\Http\Controllers\Admin\PlaceActivityController::class);
    Route::resource('places.hotels', App\Http\Controllers\Admin\PlaceHotelController::class);
    Route::resource('places.restaurants', App\Http\Controllers\Admin\PlaceRestaurantController::class);
    Route::resource('tags', App\Http\Controllers\Admin\TagController::class)->except(['show']);
    Route::resource('reviews', App\Http\Controllers\Admin\ReviewController::class)->only(['index', 'destroy']);
});
