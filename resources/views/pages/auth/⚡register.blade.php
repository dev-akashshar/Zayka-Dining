<?php

use Livewire\Component;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Flux\Flux;

new #[Title('Register'), Layout('layouts.auth.card')] class extends Component {
    public string $email = '';
    public string $name = '';
    public string $role = 'customer'; // customer, manager
    public float $payment_amount = 4999.00;

    // Restaurant details (only if manager)
    public string $restaurant_name = '';
    public string $restaurant_phone = '';
    public string $restaurant_address = '';

    public function mount(): void
    {
        $this->email = session('register_email', '');

        if (empty($this->email)) {
            $this->redirect(route('login'), navigate: true);
        }

        // Determine plan price from session
        $plan = session('selected_plan');
        if ($plan === '1999' || $plan === 'starter') {
            $this->payment_amount = 1999.00;
        } elseif ($plan === '9999' || $plan === 'maharaja') {
            $this->payment_amount = 9999.00;
        } else {
            $this->payment_amount = 4999.00;
        }
    }

    public function register(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'role' => 'required|in:customer,manager',
        ];

        if ($this->role === 'manager') {
            $rules['restaurant_name'] = 'required|string|max:255';
            $rules['restaurant_phone'] = 'required|string|max:255';
            $rules['restaurant_address'] = 'required|string';
        }

        $this->validate($rules);

        // Ensure roles exist
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'customer']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'manager']);

        // If registering as a manager, we create a Manager Request instead of the active user/restaurant
        if ($this->role === 'manager') {
            if (User::where('email', $this->email)->exists() || \App\Models\ManagerRequest::where('email', $this->email)->where('status', '!=', 'rejected')->exists()) {
                $this->addError('email', __('This email is already registered or has a pending request.'));
                return;
            }

            // Create Manager Request
            $managerRequest = \App\Models\ManagerRequest::create([
                'name' => $this->name,
                'email' => $this->email,
                'restaurant_name' => $this->restaurant_name,
                'restaurant_phone' => $this->restaurant_phone,
                'restaurant_address' => $this->restaurant_address,
                'payment_amount' => $this->payment_amount,
                'payment_status' => 'pending',
                'status' => 'pending_payment',
            ]);

            // Clean up session
            session()->forget(['register_email', 'otp_email', 'selected_plan']);

            Flux::toast(variant: 'success', text: __('Redirecting to payment...'));
            $this->redirect(route('manager-request.pay', ['id' => $managerRequest->id]));
            return;
        }

        // Create Customer User directly
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make(Str::random(32)), // Random password since we use OTP
        ]);

        $user->assignRole('customer');
        $user->markEmailAsVerified();

        // Login
        Auth::login($user, remember: true);

        // Clean up session
        session()->forget(['register_email', 'otp_email', 'selected_plan']);

        Flux::toast(variant: 'success', text: __('Registration complete. Welcome!'));
        $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6 w-full">
    <x-auth-header :title="__('Create an account')" :description="__('Complete registration for ' . $email)" />

    <form wire:submit="register" class="flex flex-col gap-4">
        <flux:input
            wire:model="name"
            name="name"
            :label="__('Full Name')"
            type="text"
            required
            autofocus
            placeholder="John Doe"
        />

        <flux:radio.group wire:model.live="role" :label="__('I want to register as a')" variant="cards" class="grid grid-cols-2 gap-4">
            <flux:radio value="customer" :label="__('Customer')" :description="__('Book tables and pay online')" />
            <flux:radio value="manager" :label="__('Restaurant Owner')" :description="__('Manage my restaurant profile, tables, and staff')" />
        </flux:radio.group>

        @if ($role === 'manager')
            <div class="space-y-4 border-t border-neutral-100 dark:border-neutral-800 pt-4 mt-2">
                <flux:heading size="lg">{{ __('Restaurant Profile') }}</flux:heading>

                <flux:input
                    wire:model="restaurant_name"
                    name="restaurant_name"
                    :label="__('Restaurant Name')"
                    type="text"
                    required
                    placeholder="Gourmet Bistro"
                />

                <flux:input
                    wire:model="restaurant_phone"
                    name="restaurant_phone"
                    :label="__('Contact Phone')"
                    type="text"
                    required
                    placeholder="+91 98765 43210"
                />

                <flux:textarea
                    wire:model="restaurant_address"
                    name="restaurant_address"
                    :label="__('Street Address')"
                    required
                    placeholder="123 Food Street, Culinary City"
                />
            </div>
        @endif

        <div class="flex items-center justify-end mt-4">
            <flux:button variant="primary" type="submit" class="w-full">
                {{ $role === 'manager' ? __('Proceed to Payment (₹' . number_format($payment_amount) . ')') : __('Complete Registration') }}
            </flux:button>
        </div>
    </form>
</div>
