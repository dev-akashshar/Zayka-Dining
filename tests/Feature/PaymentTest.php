<?php

use App\Models\Booking;
use App\Models\Restaurant;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'customer']);
    Role::firstOrCreate(['name' => 'manager']);
    Role::firstOrCreate(['name' => 'staff']);

    $this->restaurant = Restaurant::create([
        'name' => 'Bella Italia',
        'slug' => 'bella-italia',
        'is_active' => true,
    ]);

    $this->customer = User::create([
        'name' => 'Alice Customer',
        'email' => 'alice@customer.com',
        'password' => bcrypt('password'),
    ]);
    $this->customer->assignRole('customer');

    $this->otherCustomer = User::create([
        'name' => 'Bob Customer',
        'email' => 'bob@customer.com',
        'password' => bcrypt('password'),
    ]);
    $this->otherCustomer->assignRole('customer');

    $this->manager = User::create([
        'name' => 'Manager Marco',
        'email' => 'manager@resto.com',
        'password' => bcrypt('password'),
        'restaurant_id' => $this->restaurant->id,
    ]);
    $this->manager->assignRole('manager');

    $this->booking = Booking::create([
        'restaurant_id' => $this->restaurant->id,
        'user_id' => $this->customer->id,
        'guest_count' => 2,
        'booking_date' => now()->toDateString(),
        'booking_time' => '19:00:00',
        'payment_type' => 'full',
        'payment_amount' => 50.00,
        'payment_status' => 'unpaid',
    ]);
});

test('successful payment session updates booking status', function () {
    $this->actingAs($this->customer);

    $response = $this->get(route('booking.success', [
        'id' => $this->booking->id,
        'session_id' => 'stripe_session_12345',
    ]));

    $response->assertRedirect(route('dashboard'));

    $freshBooking = $this->booking->fresh();
    expect($freshBooking->payment_status)->toBe('paid_full');
    expect($freshBooking->stripe_payment_intent_id)->toBe('stripe_session_12345');
});

test('authorized users can download booking invoices', function () {
    // Make booking paid
    $this->booking->update(['payment_status' => 'paid_full']);

    // 1. Customer who made booking can download
    $this->actingAs($this->customer);
    $response = $this->get(route('booking.invoice', ['id' => $this->booking->id]));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');

    // 2. Manager of restaurant can download
    $this->actingAs($this->manager);
    $response = $this->get(route('booking.invoice', ['id' => $this->booking->id]));
    $response->assertStatus(200);

    // 3. Unauthorized customer gets 403
    $this->actingAs($this->otherCustomer);
    $response = $this->get(route('booking.invoice', ['id' => $this->booking->id]));
    $response->assertStatus(403);
});
