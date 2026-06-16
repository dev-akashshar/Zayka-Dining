<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $checkIn  = $this->faker->dateTimeBetween('-7 days', 'now');
        $checkOut = (clone $checkIn)->modify('+8 hours +45 minutes');

        return [
            'user_id'       => User::factory(),
            'restaurant_id' => Restaurant::factory(),
            'check_in'      => $checkIn,
            'check_out'     => $checkOut,
            'notes'         => $this->faker->randomElement([
                'Managed kitchen preparation shift.',
                'Front desk and reservations duty.',
                'Evening banquet service.',
                'Morning mise-en-place setup.',
                'Active check-in morning duty roster.',
            ]),
        ];
    }

    /**
     * State: active shift (checked in, no check-out yet).
     */
    public function active(): static
    {
        return $this->state(fn () => ['check_out' => null]);
    }

    /**
     * State: yesterday's completed shift.
     */
    public function yesterday(): static
    {
        return $this->state(function () {
            $checkIn  = now()->subDay()->setHour(9)->setMinute(30);
            $checkOut = now()->subDay()->setHour(18)->setMinute(15);
            return [
                'check_in'  => $checkIn,
                'check_out' => $checkOut,
                'notes'     => 'Managed kitchen preparation shift.',
            ];
        });
    }
}
