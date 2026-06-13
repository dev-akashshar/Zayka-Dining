<?php

use Livewire\Component;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Booking;
use Spatie\Permission\Models\Role;
use Livewire\Attributes\Computed;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Flux\Flux;

new class extends Component {
    // New restaurant fields
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';

    // Selected user role management
    public int $userIdToManage;
    public string $newRole = 'customer';

    public function createRestaurant(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string',
            'address' => 'required|string',
        ]);

        $restaurant = Restaurant::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name) . '-' . Str::random(5),
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'is_active' => true,
        ]);

        // Seed default tables for the restaurant
        for ($i = 1; $i <= 5; $i++) {
            $restaurant->tables()->create([
                'name' => "Table {$i}",
                'capacity' => $i <= 2 ? 2 : ($i <= 4 ? 4 : 6),
                'status' => 'available'
            ]);
        }

        Flux::toast(variant: 'success', text: __('Restaurant and default tables created.'));
        $this->reset(['name', 'email', 'phone', 'address']);
    }

    public function toggleRestaurantStatus(int $id): void
    {
        $restaurant = Restaurant::findOrFail($id);
        $restaurant->update(['is_active' => ! $restaurant->is_active]);

        Flux::toast(variant: 'success', text: __('Restaurant status updated.'));
    }

    public function changeUserRole(int $id, string $role): void
    {
        $user = User::findOrFail($id);
        $user->syncRoles([$role]);

        Flux::toast(variant: 'success', text: __('User role updated successfully.'));
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'total_restaurants' => Restaurant::count(),
            'total_users' => User::count(),
            'total_bookings' => Booking::count(),
            'total_revenue' => Booking::whereIn('payment_status', ['paid_full', 'paid_advance'])->sum('payment_amount'),
        ];
    }

    #[Computed]
    public function restaurants(): \Illuminate\Database\Eloquent\Collection
    {
        return Restaurant::withCount('bookings')->latest()->get();
    }

    #[Computed]
    public function users(): \Illuminate\Database\Eloquent\Collection
    {
        return User::with('roles', 'restaurant')->latest()->get();
    }

    #[Computed]
    public function bookings(): \Illuminate\Database\Eloquent\Collection
    {
        return Booking::with(['restaurant', 'user'])->latest()->take(10)->get();
    }

    #[Computed]
    public function pendingRequests(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\ManagerRequest::where('payment_status', 'paid')
            ->where('status', 'pending_approval')
            ->latest()
            ->get();
    }

    public function approveManagerRequest(int $requestId): void
    {
        $req = \App\Models\ManagerRequest::findOrFail($requestId);

        if (User::where('email', $req->email)->exists()) {
            Flux::toast(variant: 'danger', text: __('A user with this email already exists.'));
            return;
        }

        // 1. Create User
        $user = User::create([
            'name' => $req->name,
            'email' => $req->email,
            'password' => Hash::make(Str::random(32)),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('manager');

        // 2. Create Restaurant
        $restaurant = Restaurant::create([
            'name' => $req->restaurant_name,
            'slug' => Str::slug($req->restaurant_name) . '-' . Str::random(5),
            'email' => $req->email,
            'phone' => $req->restaurant_phone,
            'address' => $req->restaurant_address,
            'is_active' => true,
        ]);

        // 3. Link manager
        $user->restaurant_id = $restaurant->id;
        $user->save();

        // 4. Seed default tables
        for ($i = 1; $i <= 5; $i++) {
            $restaurant->tables()->create([
                'name' => "Table {$i}",
                'capacity' => $i <= 2 ? 2 : ($i <= 4 ? 4 : 6),
                'status' => 'available'
            ]);
        }

        // 5. Update request status
        $req->update(['status' => 'approved']);

        Flux::toast(variant: 'success', text: __('Manager registered and restaurant provisioned successfully!'));

        // Refresh properties
        unset($this->pendingRequests);
        unset($this->restaurants);
        unset($this->users);
    }
}; ?>

    <div class="space-y-8 p-6 max-w-7xl mx-auto"
         x-data="{
             init() {
                 const checkHash = () => {
                     const hash = window.location.hash;
                     if (!hash) return;
                     setTimeout(() => {
                         const el = document.querySelector(hash);
                         if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                     }, 100);
                 };
                 window.addEventListener('hashchange', checkHash);
                 checkHash();
             }
         }">
        <!-- Dashboard Header -->
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('System Administration') }}</flux:heading>
                <flux:subheading>{{ __('Monitor restaurants, manage users, and inspect platform analytics') }}</flux:subheading>
            </div>
            <flux:badge color="rose" class="text-sm font-semibold uppercase">{{ __('Super Admin') }}</flux:badge>
        </div>

        <!-- KPI Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs flex items-center justify-between">
                <div>
                    <flux:text size="sm" class="text-neutral-500">{{ __('Restaurants') }}</flux:text>
                    <flux:heading size="xl" class="mt-1 font-bold">{{ $this->stats['total_restaurants'] }}</flux:heading>
                </div>
                <flux:icon name="building-storefront" class="size-8 text-indigo-500" />
            </div>

            <div class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs flex items-center justify-between">
                <div>
                    <flux:text size="sm" class="text-neutral-500">{{ __('Registered Users') }}</flux:text>
                    <flux:heading size="xl" class="mt-1 font-bold">{{ $this->stats['total_users'] }}</flux:heading>
                </div>
                <flux:icon name="users" class="size-8 text-indigo-500" />
            </div>

            <div class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs flex items-center justify-between">
                <div>
                    <flux:text size="sm" class="text-neutral-500">{{ __('Reservations') }}</flux:text>
                    <flux:heading size="xl" class="mt-1 font-bold">{{ $this->stats['total_bookings'] }}</flux:heading>
                </div>
                <flux:icon name="calendar-days" class="size-8 text-indigo-500" />
            </div>

            <div class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs flex items-center justify-between">
                <div>
                    <flux:text size="sm" class="text-neutral-500">{{ __('Total Platform Sales') }}</flux:text>
                    <flux:heading size="xl" class="mt-1 font-bold text-green-600 dark:text-green-400">₹{{ number_format($this->stats['total_revenue'], 2) }}</flux:heading>
                </div>
                <flux:icon name="banknotes" class="size-8 text-green-500" />
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Side: Manage Restaurants -->
            <div class="lg:col-span-2 space-y-6">
                @if ($this->pendingRequests->isNotEmpty())
                    <!-- Pending Manager Requests -->
                    <div class="bg-white dark:bg-stone-900 border border-amber-200 dark:border-amber-900/40 rounded-xl p-6 shadow-xs">
                        <div class="flex items-center justify-between mb-4">
                            <flux:heading size="lg" class="text-amber-800 dark:text-amber-400 flex items-center gap-2">
                                <flux:icon name="clock" class="size-5" />
                                {{ __('Pending Manager Approvals') }}
                            </flux:heading>
                            <flux:badge color="amber">{{ $this->pendingRequests->count() }}</flux:badge>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-neutral-100 dark:border-neutral-800 text-sm">
                                        <th class="py-2 font-semibold text-neutral-500">{{ __('Manager') }}</th>
                                        <th class="py-2 font-semibold text-neutral-500">{{ __('Restaurant') }}</th>
                                        <th class="py-2 font-semibold text-neutral-500 text-center">{{ __('Payment') }}</th>
                                        <th class="py-2 font-semibold text-neutral-500 text-right">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($this->pendingRequests as $req)
                                        <tr class="border-b border-neutral-50 dark:border-neutral-800/40 text-sm hover:bg-neutral-50/50">
                                            <td class="py-3">
                                                <div class="font-bold text-neutral-800 dark:text-neutral-200">{{ $req->name }}</div>
                                                <div class="text-xs text-neutral-500">{{ $req->email }}</div>
                                            </td>
                                            <td class="py-3">
                                                <div class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $req->restaurant_name }}</div>
                                                <div class="text-xs text-neutral-500">{{ $req->restaurant_phone }}</div>
                                                <div class="text-xs text-neutral-400 truncate max-w-xs">{{ $req->restaurant_address }}</div>
                                            </td>
                                            <td class="py-3 text-center">
                                                <flux:badge color="green">Paid ₹{{ number_format($req->payment_amount, 0) }}</flux:badge>
                                            </td>
                                            <td class="py-3 text-right">
                                                <flux:button size="xs" variant="primary" class="bg-amber-600 hover:bg-amber-700 text-white" wire:click="approveManagerRequest({{ $req->id }})">
                                                    {{ __('Approve & Create') }}
                                                </flux:button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Restaurant list -->
                <div id="restaurants-management" class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs">
                    <flux:heading size="lg" class="mb-4">{{ __('Manage Restaurants') }}</flux:heading>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-neutral-100 dark:border-neutral-800">
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Name') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Contact') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Bookings') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Status') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-right">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->restaurants as $resto)
                                    <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-800/20">
                                        <td class="py-3 font-medium text-neutral-800 dark:text-neutral-200">
                                            {{ $resto->name }}
                                        </td>
                                        <td class="py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                            <div>{{ $resto->phone }}</div>
                                            <div class="text-xs">{{ $resto->email }}</div>
                                        </td>
                                        <td class="py-3 text-center text-sm">
                                            {{ $resto->bookings_count }}
                                        </td>
                                        <td class="py-3 text-center">
                                            <flux:badge color="{{ $resto->is_active ? 'green' : 'amber' }}">
                                                {{ $resto->is_active ? __('Active') : __('Suspended') }}
                                            </flux:badge>
                                        </td>
                                        <td class="py-3 text-right">
                                            <flux:button size="sm" variant="ghost" wire:click="toggleRestaurantStatus({{ $resto->id }})">
                                                {{ $resto->is_active ? __('Suspend') : __('Activate') }}
                                            </flux:button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- User Management -->
                <div id="users-management" class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs">
                    <flux:heading id="roles-management" size="lg" class="mb-4">{{ __('Manage Platform Users') }}</flux:heading>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-neutral-100 dark:border-neutral-800">
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Name/Email') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Restaurant') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Role') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-right">{{ __('Change Role') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->users as $user)
                                    <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-neutral-50/50">
                                        <td class="py-3">
                                            <div class="font-medium">{{ $user->name }}</div>
                                            <div class="text-xs text-neutral-500">{{ $user->email }}</div>
                                        </td>
                                        <td class="py-3 text-sm">
                                            {{ $user->restaurant ? $user->restaurant->name : '-' }}
                                        </td>
                                        <td class="py-3">
                                            <flux:badge color="indigo">
                                                {{ $user->roles->pluck('name')->first() ?? 'customer' }}
                                            </flux:badge>
                                        </td>
                                        <td class="py-3 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <flux:button size="xs" variant="ghost" wire:click="changeUserRole({{ $user->id }}, 'manager')">Manager</flux:button>
                                                <flux:button size="xs" variant="ghost" wire:click="changeUserRole({{ $user->id }}, 'staff')">Staff</flux:button>
                                                <flux:button size="xs" variant="ghost" wire:click="changeUserRole({{ $user->id }}, 'customer')">Customer</flux:button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Side: Create Restaurant Panel -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs">
                    <flux:heading size="lg" class="mb-4">{{ __('Add New Restaurant') }}</flux:heading>
                    <form wire:submit="createRestaurant" class="space-y-4">
                        <flux:input wire:model="name" :label="__('Restaurant Name')" type="text" required placeholder="La Piazza" />
                        <flux:input wire:model="email" :label="__('Email Address')" type="email" required placeholder="contact@lapiazza.com" />
                        <flux:input wire:model="phone" :label="__('Contact Phone')" type="text" required placeholder="+1 (555) 018-2938" />
                        <flux:textarea wire:model="address" :label="__('Street Address')" required placeholder="789 Italy Way, Venice" />

                        <flux:button variant="primary" type="submit" class="w-full mt-4 bg-indigo-600 hover:bg-indigo-700">
                            {{ __('Provision Restaurant') }}
                        </flux:button>
                    </form>
                </div>

                <!-- Recent Payments & Bookings Log -->
                <div id="audit-logs" class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs">
                    <flux:heading size="lg" class="mb-4">{{ __('Recent Reservation Audits') }}</flux:heading>
                    <div class="space-y-4">
                        @foreach ($this->bookings as $b)
                            <div class="flex items-start justify-between text-sm border-b border-neutral-100 dark:border-neutral-800 pb-2">
                                <div>
                                    <div class="font-medium text-neutral-800 dark:text-neutral-200">
                                        {{ $b->user->name }}
                                    </div>
                                    <div class="text-xs text-neutral-500">
                                        {{ $b->restaurant->name }} - {{ $b->booking_date->format('M d') }} @ {{ \Carbon\Carbon::parse($b->booking_time)->format('h:i A') }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-green-600 dark:text-green-400">
                                        ₹{{ number_format($b->payment_amount, 2) }}
                                    </div>
                                    <flux:badge size="xs" color="{{ $b->status === 'confirmed' ? 'green' : ($b->status === 'pending' ? 'amber' : 'red') }}">
                                        {{ $b->status }}
                                    </flux:badge>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
