<?php

use App\Models\Restaurant;
use App\Models\User;
use App\Models\Attendance;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'staff']);
    Role::firstOrCreate(['name' => 'manager']);

    $this->restaurant = Restaurant::create([
        'name' => 'Bella Italia',
        'slug' => 'bella-italia',
        'is_active' => true,
    ]);

    $this->staff = User::create([
        'name' => 'Luigi Mario',
        'email' => 'luigi@example.com',
        'password' => bcrypt('password'),
        'restaurant_id' => $this->restaurant->id,
    ]);
    $this->staff->assignRole('staff');
});

test('staff member can check in for a shift', function () {
    $this->actingAs($this->staff);

    expect(Attendance::count())->toBe(0);

    // Create attendance record
    $attendance = Attendance::create([
        'user_id' => $this->staff->id,
        'restaurant_id' => $this->restaurant->id,
        'check_in' => now(),
        'notes' => 'Starting morning floor shift.',
    ]);

    expect(Attendance::count())->toBe(1);
    expect($attendance->check_out)->toBeNull();
    expect($attendance->notes)->toBe('Starting morning floor shift.');
});

test('staff member can check out of a shift', function () {
    $this->actingAs($this->staff);

    // Seed check-in record
    $attendance = Attendance::create([
        'user_id' => $this->staff->id,
        'restaurant_id' => $this->restaurant->id,
        'check_in' => now()->subHours(8),
    ]);

    // Check out
    $attendance->update([
        'check_out' => now(),
    ]);

    expect($attendance->check_out)->not->toBeNull();
    expect($attendance->check_in->diffInHours($attendance->check_out))->toEqual(8);
});
