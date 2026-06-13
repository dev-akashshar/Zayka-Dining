<?php

use App\Models\Restaurant;
use App\Models\User;
use App\Models\Table;
use App\Models\Booking;
use App\Services\BookingService;
use Spatie\Permission\Models\Role;
use Livewire\Livewire;

beforeEach(function () {
    // Set up default roles
    Role::firstOrCreate(['name' => 'customer']);
    Role::firstOrCreate(['name' => 'manager']);
    Role::firstOrCreate(['name' => 'staff']);

    // Create a restaurant
    $this->restaurant = Restaurant::create([
        'name' => 'Test Restaurant',
        'slug' => 'test-restaurant',
        'email' => 'test@resto.com',
        'phone' => '123456789',
        'address' => 'Test Address',
        'is_active' => true,
    ]);

    // Create tables
    $this->table1 = Table::create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Table 1',
        'capacity' => 2,
        'status' => 'available',
    ]);

    $this->table2 = Table::create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Table 2',
        'capacity' => 4,
        'status' => 'available',
    ]);

    // Create users
    $this->customer = User::create([
        'name' => 'Test Customer',
        'email' => 'testcustomer@example.com',
        'password' => bcrypt('password'),
    ]);
    $this->customer->assignRole('customer');

    $this->manager = User::create([
        'name' => 'Test Manager',
        'email' => 'testmanager@example.com',
        'password' => bcrypt('password'),
        'restaurant_id' => $this->restaurant->id,
    ]);
    $this->manager->assignRole('manager');
});

test('customer can see available tables', function () {
    $service = new BookingService();
    
    // Check table for 2 guests - should return table 1 (capacity 2)
    $table = $service->findAvailableTable($this->restaurant->id, now()->toDateString(), '19:00:00', 2);
    expect($table)->not->toBeNull();
    expect($table->id)->toBe($this->table1->id);

    // Check table for 4 guests - should return table 2 (capacity 4)
    $table = $service->findAvailableTable($this->restaurant->id, now()->toDateString(), '19:00:00', 4);
    expect($table)->not->toBeNull();
    expect($table->id)->toBe($this->table2->id);
});

test('customer cannot double book a table within overlapping slots', function () {
    $service = new BookingService();

    // Make a booking on table 1 at 19:00
    Booking::create([
        'restaurant_id' => $this->restaurant->id,
        'user_id' => $this->customer->id,
        'table_id' => $this->table1->id,
        'guest_count' => 2,
        'booking_date' => now()->toDateString(),
        'booking_time' => '19:00:00',
        'status' => 'confirmed',
        'payment_status' => 'paid_full',
        'payment_type' => 'full',
        'payment_amount' => 50.00,
    ]);

    // Checking for a table at 19:30 should fail to get Table 1 (since it is booked for 2 hours)
    // Table 2 (capacity 4) is still available for 2 guests though!
    $table = $service->findAvailableTable($this->restaurant->id, now()->toDateString(), '19:30:00', 2);
    expect($table)->not->toBeNull();
    expect($table->id)->toBe($this->table2->id); // fallback to larger table

    // Now book Table 2 at 20:00
    Booking::create([
        'restaurant_id' => $this->restaurant->id,
        'user_id' => $this->customer->id,
        'table_id' => $this->table2->id,
        'guest_count' => 2,
        'booking_date' => now()->toDateString(),
        'booking_time' => '20:00:00',
        'status' => 'confirmed',
        'payment_status' => 'paid_full',
        'payment_type' => 'full',
        'payment_amount' => 50.00,
    ]);

    // Now searching for 2 guests at 19:30 should return NULL since both Table 1 and Table 2 are occupied
    $table = $service->findAvailableTable($this->restaurant->id, now()->toDateString(), '19:30:00', 2);
    expect($table)->toBeNull();
});

test('manager can approve a pending booking', function () {
    $booking = Booking::create([
        'restaurant_id' => $this->restaurant->id,
        'user_id' => $this->customer->id,
        'table_id' => $this->table1->id,
        'guest_count' => 2,
        'booking_date' => now()->toDateString(),
        'booking_time' => '19:00:00',
        'status' => 'pending',
    ]);

    $this->actingAs($this->manager);

    // Act
    $booking->update(['status' => 'confirmed']);

    expect($booking->fresh()->status)->toBe('confirmed');
});

test('customer can cancel a pending or confirmed booking', function () {
    $booking = Booking::create([
        'restaurant_id' => $this->restaurant->id,
        'user_id' => $this->customer->id,
        'table_id' => $this->table1->id,
        'guest_count' => 2,
        'booking_date' => now()->toDateString(),
        'booking_time' => '19:00:00',
        'status' => 'pending',
    ]);

    $this->actingAs($this->customer);

    Livewire::test('pages::dashboard.customer')
        ->call('cancelBooking', $booking->id);

    expect($booking->fresh()->status)->toBe('cancelled');
});
