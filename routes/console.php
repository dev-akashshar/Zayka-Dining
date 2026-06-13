<?php

use App\Models\Booking;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled table reservation reminders
Schedule::call(function () {
    $tomorrow = now()->addDay()->toDateString();
    
    $bookings = Booking::where('booking_date', $tomorrow)
        ->where('status', 'confirmed')
        ->get();

    foreach ($bookings as $booking) {
        try {
            $booking->user->notify(new \App\Notifications\BookingStatusNotification($booking, 'reminder'));
        } catch (\Exception $e) {
            logger()->error("Failed to send scheduled reminder for booking #{$booking->id}: " . $e->getMessage());
        }
    }
})->hourly();
