<?php

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Public front-end features
Route::livewire('restaurant/{id}', 'pages::restaurant-landing')->name('restaurant.landing');
Route::livewire('blogs', 'pages::blogs')->name('blogs');
Route::view('about', 'pages.about')->name('about');
Route::livewire('contact', 'pages::contact')->name('contact');

// Guest authentication routes
Route::middleware(['guest'])->group(function () {
    Route::livewire('login', 'pages::auth.login')->name('login');
    Route::livewire('login/verify', 'pages::auth.verify-otp')->name('login.verify');
    Route::livewire('register', 'pages::auth.register')->name('register');
});

// Manager registration payment routes
Route::get('manager-request/pay/{id}', [StripeController::class, 'managerCheckout'])->name('manager-request.pay');
Route::livewire('manager-request/pay/mock/{id}', 'pages::mock-manager-checkout')->name('manager-request.pay.mock');
Route::get('manager-request/success/{id}', [StripeController::class, 'managerSuccess'])->name('manager-request.success');

// Authenticated dashboard and settings
Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');
    
    // Booking checkout & payment routes
    Route::get('booking/pay/{id}', [StripeController::class, 'checkout'])->name('booking.pay');
    Route::livewire('booking/pay/mock/{id}', 'pages::mock-checkout')->name('booking.pay.mock');
    Route::get('booking/success/{id}', [StripeController::class, 'success'])->name('booking.success');
    Route::get('booking/invoice/{id}', [InvoiceController::class, 'download'])->name('booking.invoice');
});

require __DIR__.'/settings.php';
