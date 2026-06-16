<?php

use App\Models\ManagerRequest;
use App\Models\Restaurant;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin']);
    Role::firstOrCreate(['name' => 'manager']);
    Role::firstOrCreate(['name' => 'customer']);

    $this->admin = User::create([
        'name' => 'System Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);
    $this->admin->assignRole('super_admin');
});

test('manager registration redirects to payment and creates pending request', function () {
    // Simulate email verified in session
    session(['register_email' => 'candidate@manager.com']);

    $component = Livewire::test('pages::auth.register')
        ->set('name', 'Candidate Manager')
        ->set('role', 'manager')
        ->set('restaurant_name', 'Masala House')
        ->set('restaurant_phone', '9876543210')
        ->set('restaurant_address', '123 Spice Street, Mumbai')
        ->call('register');

    $component->assertHasNoErrors();

    // Verify ManagerRequest database record
    $request = ManagerRequest::where('email', 'candidate@manager.com')->first();
    expect($request)->not->toBeNull();
    expect($request->payment_status)->toBe('pending');
    expect($request->status)->toBe('pending_payment');

    // Assert redirection to pay route
    $component->assertRedirect(route('manager-request.pay', ['id' => $request->id]));
});

test('successful payment updates manager request status', function () {
    $request = ManagerRequest::create([
        'name' => 'Paid Manager',
        'email' => 'paid@manager.com',
        'restaurant_name' => 'Royal Tandoor',
        'restaurant_phone' => '1234567890',
        'restaurant_address' => '456 Royal Lane, Delhi',
        'payment_amount' => 4999.00,
        'payment_status' => 'pending',
        'status' => 'pending_payment',
    ]);

    $response = $this->get(route('manager-request.success', ['id' => $request->id, 'session_id' => 'mock_session_999']));

    $response->assertRedirect(route('home'));
    $response->assertSessionHas('status', 'manager-request-submitted');

    $request->refresh();
    expect($request->payment_status)->toBe('paid');
    expect($request->status)->toBe('pending_approval');
    expect($request->stripe_session_id)->toBe('mock_session_999');
});

test('super admin can approve paid request to register manager and restaurant', function () {
    $request = ManagerRequest::create([
        'name' => 'Approved Manager',
        'email' => 'approved@manager.com',
        'restaurant_name' => 'Spice Bistro',
        'restaurant_phone' => '1112223333',
        'restaurant_address' => '789 Pepper Road, Bangalore',
        'payment_amount' => 4999.00,
        'payment_status' => 'paid',
        'status' => 'pending_approval',
    ]);

    // Act as Super Admin and call Livewire action
    $this->actingAs($this->admin);

    $component = Livewire::test('pages::dashboard.super-admin')
        ->call('approveManagerRequest', $request->id);

    $component->assertHasNoErrors();

    // Assert request status updated to approved
    $request->refresh();
    expect($request->status)->toBe('approved');

    // Assert User created
    $user = User::where('email', 'approved@manager.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('manager'))->toBeTrue();

    // Assert Restaurant created
    $restaurant = Restaurant::where('email', 'approved@manager.com')->first();
    expect($restaurant)->not->toBeNull();
    expect($restaurant->name)->toBe('Spice Bistro');
    expect($user->restaurant_id)->toBe($restaurant->id);

    // Assert default tables seeded
    expect($restaurant->tables()->count())->toBe(5);
});
