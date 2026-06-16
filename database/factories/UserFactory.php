<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    protected static array $indianNames = [
        'Aarav Mehta', 'Diya Sharma', 'Kabir Patel', 'Ishaan Iyer', 'Ananya Roy',
        'Rohan Gupta', 'Pooja Reddy', 'Arjun Kapoor', 'Sneha Rao', 'Devendra Yadav',
        'Priya Singh', 'Vikram Nair', 'Shreya Joshi', 'Aditya Kumar', 'Neha Verma',
    ];

    public function definition(): array
    {
        return [
            'name'              => $this->faker->randomElement(self::$indianNames),
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
            'restaurant_id'     => null,
        ];
    }

    // ── Role states ──────────────────────────────────────────────────────────

    public function superAdmin(): static
    {
        return $this->state(fn () => [
            'name'          => 'Zayka Admin',
            'email'         => 'admin@gmail.com',
            'restaurant_id' => null,
        ])->afterCreating(fn (User $u) => $u->assignRole('super_admin'));
    }

    public function manager(int $restaurantId): static
    {
        return $this->state(fn () => [
            'restaurant_id' => $restaurantId,
        ])->afterCreating(fn (User $u) => $u->assignRole('manager'));
    }

    public function staff(int $restaurantId): static
    {
        return $this->state(fn () => [
            'restaurant_id' => $restaurantId,
        ])->afterCreating(fn (User $u) => $u->assignRole('staff'));
    }

    public function customer(): static
    {
        return $this->state(fn () => [
            'restaurant_id' => null,
        ])->afterCreating(fn (User $u) => $u->assignRole('customer'));
    }

    // ── Utility states ───────────────────────────────────────────────────────

    /** Unverified email — useful for auth tests. */
    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    /** Known test customer with a fixed email. */
    public function testCustomer(): static
    {
        return $this->state(fn () => [
            'name'  => 'Test Customer',
            'email' => 'customer@example.com',
        ])->afterCreating(fn (User $u) => $u->assignRole('customer'));
    }
}
