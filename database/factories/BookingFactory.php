<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $status     = $this->faker->randomElement(['confirmed', 'pending', 'cancelled', 'rejected', 'completed']);
        $payStatus  = match ($status) {
            'confirmed', 'completed' => 'paid_full',
            'pending'                => 'paid_advance',
            default                  => 'unpaid',
        };
        $payType    = match ($payStatus) {
            'paid_full'    => 'full',
            'paid_advance' => 'advance',
            default        => 'none',
        };
        $amount     = match ($payType) {
            'full'    => 4999.00,
            'advance' => 1250.00,
            default   => 0.00,
        };

        return [
            'restaurant_id'  => Restaurant::factory(),
            'user_id'        => User::factory(),
            'table_id'       => Table::factory(),
            'guest_count'    => $this->faker->numberBetween(1, 6),
            'booking_date'   => $this->faker->dateTimeBetween('-2 days', '+7 days')->format('Y-m-d'),
            'booking_time'   => $this->faker->randomElement(['12:00:00', '13:00:00', '14:00:00', '19:00:00', '20:00:00', '21:00:00']),
            'status'         => $status,
            'payment_status' => $payStatus,
            'payment_type'   => $payType,
            'payment_amount' => $amount,
            'notes'          => 'Aesthetic royal seating preferred.',
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status'         => 'confirmed',
            'payment_status' => 'paid_full',
            'payment_type'   => 'full',
            'payment_amount' => 4999.00,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status'         => 'pending',
            'payment_status' => 'paid_advance',
            'payment_type'   => 'advance',
            'payment_amount' => 1250.00,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status'         => 'cancelled',
            'payment_status' => 'unpaid',
            'payment_type'   => 'none',
            'payment_amount' => 0.00,
        ]);
    }
}
