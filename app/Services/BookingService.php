<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Table;
use Illuminate\Support\Carbon;

class BookingService
{
    /**
     * Check if a table is available for the given restaurant, date, time, and guest count.
     */
    public function findAvailableTable(int $restaurantId, string $date, string $time, int $guestCount): ?Table
    {
        $startTime = Carbon::parse($time)->subHours(2)->toTimeString();
        $endTime = Carbon::parse($time)->addHours(2)->toTimeString();

        return Table::where('restaurant_id', $restaurantId)
            ->where('status', 'available')
            ->where('capacity', '>=', $guestCount)
            ->whereDoesntHave('bookings', function ($query) use ($date, $startTime, $endTime) {
                $query->whereDate('booking_date', $date)
                    ->whereNotIn('status', ['cancelled', 'rejected'])
                    ->where('booking_time', '>', $startTime)
                    ->where('booking_time', '<', $endTime);
            })
            ->orderBy('capacity', 'asc')
            ->first();
    }

    /**
     * Create a booking.
     */
    public function createBooking(int $restaurantId, int $userId, int $guestCount, string $date, string $time, string $paymentType, string $notes = ''): Booking
    {
        $table = $this->findAvailableTable($restaurantId, $date, $time, $guestCount);

        if (! $table) {
            throw new \Exception('No tables available for the selected time and guest count.');
        }

        // Determine price
        // Full payment: e.g. ₹4,999.00. Advance payment: ₹1,250.00 deposit.
        $amount = $paymentType === 'full' ? 4999.00 : ($paymentType === 'advance' ? 1250.00 : 0.00);

        return Booking::create([
            'restaurant_id' => $restaurantId,
            'user_id' => $userId,
            'table_id' => $table->id,
            'guest_count' => $guestCount,
            'booking_date' => $date,
            'booking_time' => $time,
            'status' => 'pending',
            'payment_status' => $paymentType === 'none' ? 'unpaid' : 'unpaid', // marked paid upon Stripe completion
            'payment_type' => $paymentType,
            'payment_amount' => $amount,
            'notes' => $notes,
        ]);
    }
}
