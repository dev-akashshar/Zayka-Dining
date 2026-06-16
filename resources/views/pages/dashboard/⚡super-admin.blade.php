<?php

use Livewire\Component;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Booking;
use Spatie\Permission\Models\Role;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Flux\Flux;

new class extends Component {
    use WithPagination;

    #[Url(keep: true)]
    public string $tab = 'dashboard';

    // Search and filter queries
    public string $searchRestaurant = '';
    public string $searchUser = '';
    public string $filterRole = '';

    // New restaurant fields
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';

    // Selected user role management
    public int $userIdToManage;
    public string $newRole = 'customer';

    // User Edit fields
    public bool $isEditingUser = false;
    public ?int $editingUserId = null;
    public string $editingUserName = '';
    public string $editingUserEmail = '';
    public ?int $editingUserRestaurantId = null;

    public function updatingSearchRestaurant(): void
    {
        $this->resetPage('restaurantsPage');
    }

    public function updatingSearchUser(): void
    {
        $this->resetPage('usersPage');
    }

    public function updatingFilterRole(): void
    {
        $this->resetPage('usersPage');
    }

    public function editUser(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingUserId = $user->id;
        $this->editingUserName = $user->name;
        $this->editingUserEmail = $user->email;
        $this->editingUserRestaurantId = $user->restaurant_id;
        $this->isEditingUser = true;
        
        // Switch to users tab just in case
        $this->tab = 'users';
    }

    public function updateUser(): void
    {
        $this->validate([
            'editingUserName' => 'required|string|max:255',
            'editingUserEmail' => 'required|email|max:255|unique:users,email,' . $this->editingUserId,
        ]);

        $user = User::findOrFail($this->editingUserId);
        $user->update([
            'name' => $this->editingUserName,
            'email' => $this->editingUserEmail,
            'restaurant_id' => $this->editingUserRestaurantId ?: null,
        ]);

        Log::info("[Super Admin] Updated user details for {$user->name} (ID: {$user->id}). Linked Restaurant ID: " . ($user->restaurant_id ?? 'None'));

        Flux::toast(variant: 'success', text: __('User account updated successfully.'));
        $this->cancelEditUser();

        // Refresh dynamic list
        unset($this->users);
    }

    public function cancelEditUser(): void
    {
        $this->reset(['isEditingUser', 'editingUserId', 'editingUserName', 'editingUserEmail', 'editingUserRestaurantId']);
    }

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

        Log::info("[Super Admin] Provisioned new restaurant: {$restaurant->name} (ID: {$restaurant->id}) with default tables.");

        Flux::toast(variant: 'success', text: __('Restaurant and default tables created.'));
        $this->reset(['name', 'email', 'phone', 'address']);
    }

    public function toggleRestaurantStatus(int $id): void
    {
        $restaurant = Restaurant::findOrFail($id);
        $restaurant->update(['is_active' => ! $restaurant->is_active]);

        Log::info("[Super Admin] Toggled restaurant status for '{$restaurant->name}' (ID: {$restaurant->id}) to: " . ($restaurant->is_active ? 'Active' : 'Suspended'));

        Flux::toast(variant: 'success', text: __('Restaurant status updated.'));
    }

    public function changeUserRole(int $id, string $role): void
    {
        $user = User::findOrFail($id);
        $user->syncRoles([$role]);

        Log::info("[Super Admin] Changed security role for user {$user->name} (ID: {$user->id}) to: {$role}");

        Flux::toast(variant: 'success', text: __('User role updated successfully.'));
        
        // Refresh users
        unset($this->users);
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
    public function restaurants(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Restaurant::withCount('bookings')
            ->when($this->searchRestaurant, function ($query) {
                $query->where('name', 'like', '%' . $this->searchRestaurant . '%')
                      ->orWhere('email', 'like', '%' . $this->searchRestaurant . '%')
                      ->orWhere('phone', 'like', '%' . $this->searchRestaurant . '%');
            })
            ->latest()
            ->paginate(10, ['*'], 'restaurantsPage');
    }

    #[Computed]
    public function allRestaurants(): \Illuminate\Database\Eloquent\Collection
    {
        return Restaurant::orderBy('name')->get();
    }

    #[Computed]
    public function users(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return User::with('roles', 'restaurant')
            ->when($this->searchUser, function ($query) {
                $query->where('name', 'like', '%' . $this->searchUser . '%')
                      ->orWhere('email', 'like', '%' . $this->searchUser . '%');
            })
            ->when($this->filterRole, function ($query) {
                $query->role($this->filterRole);
            })
            ->latest()
            ->paginate(10, ['*'], 'usersPage');
    }

    #[Computed]
    public function roleDistribution(): array
    {
        return [
            'super_admin' => User::role('super_admin')->count(),
            'manager' => User::role('manager')->count(),
            'staff' => User::role('staff')->count(),
            'customer' => User::role('customer')->count(),
        ];
    }

    #[Computed]
    public function bookings(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Booking::with(['restaurant', 'user'])
            ->latest()
            ->paginate(10, ['*'], 'bookingsPage');
    }

    #[Computed]
    public function systemLogs(): array
    {
        $logPath = storage_path('logs/laravel.log');
        if (!file_exists($logPath)) {
            return [];
        }

        $fileLines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$fileLines) {
            return [];
        }

        $entries = [];
        $currentEntry = null;

        foreach ($fileLines as $line) {
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+([a-zA-Z0-9_-]+)\.([a-zA-Z]+):\s+(.*)$/s', $line, $matches)) {
                if ($currentEntry) {
                    $entries[] = $currentEntry;
                }
                $currentEntry = [
                    'timestamp' => $matches[1],
                    'env' => $matches[2],
                    'level' => strtoupper($matches[3]),
                    'message' => $matches[4],
                    'full_message' => $matches[4]
                ];
            } else {
                if ($currentEntry) {
                    $currentEntry['message'] .= "\n" . $line;
                    $currentEntry['full_message'] .= "\n" . $line;
                } else {
                    $currentEntry = [
                        'timestamp' => now()->toDateTimeString(),
                        'env' => 'local',
                        'level' => 'INFO',
                        'message' => $line,
                        'full_message' => $line
                    ];
                }
            }
        }
        if ($currentEntry) {
            $entries[] = $currentEntry;
        }

        return array_slice(array_reverse($entries), 0, 50);
    }

    public function clearLogs(): void
    {
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            file_put_contents($logPath, '');
            Log::info("[Super Admin] Cleared system logs console.");
            Flux::toast(variant: 'success', text: __('System logs cleared successfully.'));
            
            unset($this->systemLogs);
        }
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

        Log::info("[Super Admin] Approved manager request ID {$requestId}. Provisioned user {$user->name} (ID: {$user->id}) and restaurant {$restaurant->name} (ID: {$restaurant->id}).");

        Flux::toast(variant: 'success', text: __('Manager registered and restaurant provisioned successfully!'));

        // Refresh properties
        unset($this->pendingRequests);
        unset($this->restaurants);
        unset($this->users);
    }
}; ?>

    <div class="space-y-8 p-6 max-w-7xl mx-auto transition-all duration-300">
        <!-- Dashboard Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-neutral-100 dark:border-stone-800 pb-6">
            <div>
                @if ($tab === 'dashboard')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-indigo-600 to-violet-600 dark:from-indigo-400 dark:to-violet-400 bg-clip-text text-transparent">{{ __('System Administration') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Monitor restaurants, manage users, and inspect platform analytics') }}</flux:subheading>
                @elseif ($tab === 'restaurants')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-sky-600 to-indigo-600 dark:from-sky-400 dark:to-indigo-400 bg-clip-text text-transparent">{{ __('Establishments Directory') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Manage active food joints, review details, and provision new culinary points') }}</flux:subheading>
                @elseif ($tab === 'users')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-violet-600 to-indigo-600 dark:from-violet-400 dark:to-indigo-400 bg-clip-text text-transparent">{{ __('Registered User Accounts') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Manage platform profiles, edit login credentials, and link users to establishments') }}</flux:subheading>
                @elseif ($tab === 'roles')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-fuchsia-600 to-pink-600 dark:from-fuchsia-400 dark:to-pink-400 bg-clip-text text-transparent">{{ __('Role Access Auditor') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Inspect system roles, review security permissions, and audit account access') }}</flux:subheading>
                @elseif ($tab === 'requests')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-amber-600 to-orange-600 dark:from-amber-400 dark:to-orange-400 bg-clip-text text-transparent">{{ __('Manager Signups & Requests') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Review manager onboarding requests, verify Stripe payments, and approve access') }}</flux:subheading>
                @elseif ($tab === 'audits')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-emerald-600 to-teal-600 dark:from-emerald-400 dark:to-teal-400 bg-clip-text text-transparent">{{ __('Reservation Audit Trails') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Track reservations and audit billing logs of all dining venues') }}</flux:subheading>
                @endif
            </div>
            <div>
                <flux:badge color="rose" class="text-sm font-bold px-3 py-1 shadow-sm uppercase tracking-wider bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50">{{ __('Super Admin') }}</flux:badge>
            </div>
        </div>

        @if ($tab === 'dashboard')
            <!-- Overview Tab Dashboard -->
            
            <!-- KPI Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Restaurants Card -->
                <div class="relative overflow-hidden bg-gradient-to-br from-indigo-500/5 via-white to-white dark:from-indigo-500/10 dark:via-stone-900 dark:to-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-300 group cursor-pointer" wire:click="$set('tab', 'restaurants')">
                    <div class="absolute right-0 top-0 size-24 bg-indigo-500/5 dark:bg-indigo-500/10 rounded-bl-full pointer-events-none transition-all duration-300 group-hover:scale-110"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:text size="sm" class="text-neutral-500 dark:text-neutral-400 font-medium tracking-wide uppercase">{{ __('Restaurants') }}</flux:text>
                            <flux:heading size="2xl" class="mt-2 font-black text-neutral-800 dark:text-neutral-100">{{ $this->stats['total_restaurants'] }}</flux:heading>
                        </div>
                        <div class="p-3 bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-xl">
                            <flux:icon name="building-storefront" class="size-6" />
                        </div>
                    </div>
                </div>

                <!-- Users Card -->
                <div class="relative overflow-hidden bg-gradient-to-br from-violet-500/5 via-white to-white dark:from-violet-500/10 dark:via-stone-900 dark:to-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-300 group cursor-pointer" wire:click="$set('tab', 'users')">
                    <div class="absolute right-0 top-0 size-24 bg-violet-500/5 dark:bg-violet-500/10 rounded-bl-full pointer-events-none transition-all duration-300 group-hover:scale-110"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:text size="sm" class="text-neutral-500 dark:text-neutral-400 font-medium tracking-wide uppercase">{{ __('Registered Users') }}</flux:text>
                            <flux:heading size="2xl" class="mt-2 font-black text-neutral-800 dark:text-neutral-100">{{ $this->stats['total_users'] }}</flux:heading>
                        </div>
                        <div class="p-3 bg-violet-500/10 dark:bg-violet-500/20 text-violet-600 dark:text-violet-400 rounded-xl">
                            <flux:icon name="users" class="size-6" />
                        </div>
                    </div>
                </div>

                <!-- Bookings Card -->
                <div class="relative overflow-hidden bg-gradient-to-br from-emerald-500/5 via-white to-white dark:from-emerald-500/10 dark:via-stone-900 dark:to-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-300 group cursor-pointer" wire:click="$set('tab', 'audits')">
                    <div class="absolute right-0 top-0 size-24 bg-emerald-500/5 dark:bg-emerald-500/10 rounded-bl-full pointer-events-none transition-all duration-300 group-hover:scale-110"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:text size="sm" class="text-neutral-500 dark:text-neutral-400 font-medium tracking-wide uppercase">{{ __('Reservations') }}</flux:text>
                            <flux:heading size="2xl" class="mt-2 font-black text-neutral-800 dark:text-neutral-100">{{ $this->stats['total_bookings'] }}</flux:heading>
                        </div>
                        <div class="p-3 bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-xl">
                            <flux:icon name="calendar-days" class="size-6" />
                        </div>
                    </div>
                </div>

                <!-- Revenue Card -->
                <div class="relative overflow-hidden bg-gradient-to-br from-green-500/5 via-white to-white dark:from-green-500/10 dark:via-stone-900 dark:to-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-300 group">
                    <div class="absolute right-0 top-0 size-24 bg-green-500/5 dark:bg-green-500/10 rounded-bl-full pointer-events-none transition-all duration-300 group-hover:scale-110"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:text size="sm" class="text-neutral-500 dark:text-neutral-400 font-medium tracking-wide uppercase">{{ __('Platform Sales') }}</flux:text>
                            <flux:heading size="2xl" class="mt-2 font-black text-green-600 dark:text-green-400">₹{{ number_format($this->stats['total_revenue'], 2) }}</flux:heading>
                        </div>
                        <div class="p-3 bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 rounded-xl">
                            <flux:icon name="banknotes" class="size-6" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Snapshot Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Snapshot of Recent Bookings -->
                <div class="lg:col-span-2 bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-neutral-100 dark:border-stone-800 pb-3">
                        <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Recent Booking Activities') }}</flux:heading>
                        <flux:button variant="ghost" size="xs" wire:click="$set('tab', 'audits')" class="text-indigo-600 dark:text-indigo-400 font-semibold cursor-pointer">
                            {{ __('View All') }} &rarr;
                        </flux:button>
                    </div>
                    
                    <div class="divide-y divide-neutral-100 dark:divide-stone-800">
                        @foreach ($this->bookings->take(5) as $b)
                            <div class="flex items-center justify-between py-3">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-lg">
                                        <flux:icon name="calendar" class="size-5" />
                                    </div>
                                    <div>
                                        <div class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $b->user->name }}</div>
                                        <div class="text-xs text-neutral-500">
                                            {{ $b->restaurant->name }} &bull; {{ $b->booking_date->format('M d, Y') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-neutral-800 dark:text-neutral-200">₹{{ number_format($b->payment_amount, 2) }}</div>
                                    <flux:badge size="xs" color="{{ $b->status === 'confirmed' || $b->status === 'completed' ? 'green' : ($b->status === 'pending' ? 'amber' : 'red') }}" class="font-semibold">
                                        {{ ucfirst($b->status) }}
                                    </flux:badge>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Quick Admin Navigation Options -->
                <div class="bg-gradient-to-br from-indigo-950 via-slate-900 to-stone-950 text-white rounded-2xl p-6 shadow-md flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 size-40 bg-indigo-600/20 rounded-full blur-2xl"></div>
                    <div>
                        <div class="p-3 bg-white/10 w-fit rounded-xl mb-4">
                            <flux:icon name="shield-check" class="size-6 text-indigo-400" />
                        </div>
                        <h3 class="text-xl font-bold tracking-tight mb-2">{{ __('Administrative Actions') }}</h3>
                        <p class="text-sm text-neutral-400 leading-relaxed mb-6">{{ __('Quickly switch to operational directories to perform management tasks on establishments, verify users, or change access permissions.') }}</p>
                    </div>

                    <div class="space-y-3 relative z-10">
                        <button wire:click="$set('tab', 'restaurants')" class="w-full flex items-center justify-between px-4 py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl transition-all duration-200 text-left font-semibold text-sm group">
                            <span>{{ __('Manage Establishments') }}</span>
                            <flux:icon name="chevron-right" class="size-4 text-indigo-400 transition-transform group-hover:translate-x-1" />
                        </button>
                        <button wire:click="$set('tab', 'users')" class="w-full flex items-center justify-between px-4 py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl transition-all duration-200 text-left font-semibold text-sm group">
                            <span>{{ __('Configure User Accounts') }}</span>
                            <flux:icon name="chevron-right" class="size-4 text-indigo-400 transition-transform group-hover:translate-x-1" />
                        </button>
                        <button wire:click="$set('tab', 'roles')" class="w-full flex items-center justify-between px-4 py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl transition-all duration-200 text-left font-semibold text-sm group">
                            <span>{{ __('Audit Access Roles') }}</span>
                            <flux:icon name="chevron-right" class="size-4 text-indigo-400 transition-transform group-hover:translate-x-1" />
                        </button>
                    </div>
                </div>
            </div>

        @elseif ($tab === 'restaurants')
            <!-- Establishments (Restaurants) Tab View -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Manage Restaurants Directory -->
                <div class="lg:col-span-2 bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Establishment Directory') }}</flux:heading>
                        <div class="w-full sm:w-72">
                            <flux:input wire:model.live="searchRestaurant" placeholder="Search by name or email..." icon="magnifying-glass" size="sm" class="bg-neutral-50 dark:bg-stone-950/20" />
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[750px] text-left border-collapse">
                            <thead>
                                <tr class="border-b border-neutral-100 dark:border-stone-800 text-sm">
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Restaurant Name') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Contact Details') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Total Bookings') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Status') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-50 dark:divide-stone-800/40">
                                @forelse ($this->restaurants as $resto)
                                    <tr class="hover:bg-neutral-50/50 dark:hover:bg-stone-800/20 transition-colors">
                                        <td class="py-4 font-bold text-neutral-800 dark:text-neutral-200 text-base">
                                            {{ $resto->name }}
                                        </td>
                                        <td class="py-4 text-sm text-neutral-600 dark:text-neutral-400">
                                            <div class="font-medium text-neutral-800 dark:text-neutral-200">{{ $resto->phone }}</div>
                                            <div class="text-xs text-neutral-500">{{ $resto->email }}</div>
                                        </td>
                                        <td class="py-4 text-center font-bold text-neutral-800 dark:text-neutral-200">
                                            {{ $resto->bookings_count }}
                                        </td>
                                        <td class="py-4 text-center">
                                            <flux:badge color="{{ $resto->is_active ? 'green' : 'amber' }}" class="font-bold">
                                                {{ $resto->is_active ? __('Active') : __('Suspended') }}
                                            </flux:badge>
                                        </td>
                                        <td class="py-4 text-right">
                                            <flux:button size="sm" variant="{{ $resto->is_active ? 'ghost' : 'filled' }}" wire:click="toggleRestaurantStatus({{ $resto->id }})" class="font-bold cursor-pointer">
                                                {{ $resto->is_active ? __('Suspend') : __('Activate') }}
                                            </flux:button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-neutral-500">
                                            {{ __('No establishments found matching search criteria.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-4 border-t border-neutral-100 dark:border-stone-800">
                        {{ $this->restaurants->links() }}
                    </div>
                </div>

                <!-- Add New Restaurant Panel -->
                <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs h-fit space-y-4">
                    <div>
                        <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Add New Restaurant') }}</flux:heading>
                        <flux:subheading class="text-xs">{{ __('Provision a brand new eatery onto the system.') }}</flux:subheading>
                    </div>
                    
                    <form wire:submit="createRestaurant" class="space-y-4">
                        <flux:input wire:model="name" :label="__('Restaurant Name')" type="text" required placeholder="La Piazza" class="bg-neutral-50 dark:bg-stone-950/20" />
                        <flux:input wire:model="email" :label="__('Email Address')" type="email" required placeholder="contact@lapiazza.com" class="bg-neutral-50 dark:bg-stone-950/20" />
                        <flux:input wire:model="phone" :label="__('Contact Phone')" type="text" required placeholder="+91 98765 43210" class="bg-neutral-50 dark:bg-stone-950/20" />
                        <flux:textarea wire:model="address" :label="__('Street Address')" required placeholder="789 Italy Way, Connaught Place" class="bg-neutral-50 dark:bg-stone-950/20" />

                        <flux:button variant="primary" type="submit" class="w-full mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-xl border-none">
                            {{ __('Provision Restaurant') }}
                        </flux:button>
                    </form>
                </div>
            </div>

        @elseif ($tab === 'users')
            <!-- User Accounts Tab View -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Registered Users Table -->
                <div class="lg:col-span-2 bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Registered Accounts') }}</flux:heading>
                        <div class="w-full sm:w-72">
                            <flux:input wire:model.live="searchUser" placeholder="Search by name or email..." icon="magnifying-glass" size="sm" class="bg-neutral-50 dark:bg-stone-950/20" />
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[650px] text-left border-collapse">
                            <thead>
                                <tr class="border-b border-neutral-100 dark:border-stone-800 text-sm">
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Name/Email') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Linked Establishment') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Registered On') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-right">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-50 dark:divide-stone-800/40">
                                @forelse ($this->users as $u)
                                    <tr class="hover:bg-neutral-50/50 dark:hover:bg-stone-800/20 transition-colors">
                                        <td class="py-4">
                                            <div class="font-bold text-neutral-800 dark:text-neutral-200">{{ $u->name }}</div>
                                            <div class="text-xs text-neutral-500">{{ $u->email }}</div>
                                        </td>
                                        <td class="py-4 text-sm text-neutral-600 dark:text-neutral-400 font-medium">
                                            @if ($u->restaurant)
                                                <span class="flex items-center gap-1.5">
                                                    <span class="size-1.5 rounded-full bg-indigo-500"></span>
                                                    {{ $u->restaurant->name }}
                                                </span>
                                            @else
                                                <span class="text-neutral-400 italic">{{ __('None') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-4 text-sm text-neutral-500">
                                            {{ $u->created_at ? $u->created_at->format('M d, Y') : '-' }}
                                        </td>
                                        <td class="py-4 text-right">
                                            <flux:button size="xs" variant="ghost" wire:click="editUser({{ $u->id }})" class="font-bold cursor-pointer hover:bg-neutral-100 dark:hover:bg-stone-850">
                                                {{ __('Edit Details') }}
                                            </flux:button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-neutral-500">
                                            {{ __('No user accounts found matching search criteria.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-4 border-t border-neutral-100 dark:border-stone-800">
                        {{ $this->users->links() }}
                    </div>
                </div>

                <!-- Right Side: Editing Card or Utility Tip -->
                <div class="space-y-6">
                    @if ($isEditingUser)
                        <!-- Edit User Details Form -->
                        <div class="bg-white dark:bg-stone-900 border border-indigo-200 dark:border-indigo-900/50 rounded-2xl p-6 shadow-sm space-y-4 relative overflow-hidden">
                            <div class="absolute left-0 top-0 w-1 h-full bg-indigo-500"></div>
                            <div>
                                <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Edit Account Info') }}</flux:heading>
                                <flux:subheading class="text-xs">{{ __('Modify profile fields and associate them with establishments.') }}</flux:subheading>
                            </div>
                            
                            <form wire:submit="updateUser" class="space-y-4">
                                <flux:input wire:model="editingUserName" :label="__('Display Name')" type="text" required class="bg-neutral-50 dark:bg-stone-950/20" />
                                <flux:input wire:model="editingUserEmail" :label="__('Email Address')" type="email" required class="bg-neutral-50 dark:bg-stone-950/20" />
                                
                                <flux:select wire:model="editingUserRestaurantId" :label="__('Linked Restaurant')">
                                    <option value="">{{ __('None (Independent User)') }}</option>
                                    @foreach ($this->allRestaurants as $resto)
                                        <option value="{{ $resto->id }}">{{ $resto->name }}</option>
                                    @endforeach
                                </flux:select>

                                <div class="flex items-center gap-2 pt-2">
                                    <flux:button variant="primary" type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 border-none">
                                        {{ __('Save Changes') }}
                                    </flux:button>
                                    <flux:button variant="ghost" type="button" wire:click="cancelEditUser" class="flex-1 font-bold">
                                        {{ __('Cancel') }}
                                    </flux:button>
                                </div>
                            </form>
                        </div>
                    @else
                        <!-- Operational Tips Card -->
                        <div class="bg-gradient-to-br from-indigo-50/50 to-white dark:from-stone-900 dark:to-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-4">
                            <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Account Controls') }}</flux:heading>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed">{{ __('Click "Edit Details" on any user profile to modify their core credentials, email address, or update their linked establishment. Restricting or associating managers with their corresponding restaurants is configured from here.') }}</p>
                            <div class="flex items-center gap-2 p-3 bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 rounded-xl text-xs font-semibold">
                                <flux:icon name="information-circle" class="size-5 shrink-0" />
                                <span>{{ __('Spatie roles and access logs are managed in the Role Auditor panel.') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        @elseif ($tab === 'roles')
            <!-- Role Auditor Tab View -->
            <div class="space-y-8">
                <!-- Role Distribution Metric Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-xl p-5 shadow-xs flex items-center justify-between hover:scale-[1.02] transition-transform">
                        <div>
                            <span class="text-xs font-semibold uppercase text-rose-500 tracking-wider">{{ __('Super Admins') }}</span>
                            <h4 class="text-2xl font-black mt-1 text-neutral-800 dark:text-neutral-100">{{ $this->roleDistribution['super_admin'] }}</h4>
                        </div>
                        <div class="p-2 bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 rounded-lg">
                            <flux:icon name="shield-check" class="size-5" />
                        </div>
                    </div>
                    <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-xl p-5 shadow-xs flex items-center justify-between hover:scale-[1.02] transition-transform">
                        <div>
                            <span class="text-xs font-semibold uppercase text-teal-500 tracking-wider">{{ __('Managers') }}</span>
                            <h4 class="text-2xl font-black mt-1 text-neutral-800 dark:text-neutral-100">{{ $this->roleDistribution['manager'] }}</h4>
                        </div>
                        <div class="p-2 bg-teal-50 dark:bg-teal-950/30 text-teal-600 dark:text-teal-400 rounded-lg">
                            <flux:icon name="building-storefront" class="size-5" />
                        </div>
                    </div>
                    <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-xl p-5 shadow-xs flex items-center justify-between hover:scale-[1.02] transition-transform">
                        <div>
                            <span class="text-xs font-semibold uppercase text-amber-500 tracking-wider">{{ __('Staff') }}</span>
                            <h4 class="text-2xl font-black mt-1 text-neutral-800 dark:text-neutral-100">{{ $this->roleDistribution['staff'] }}</h4>
                        </div>
                        <div class="p-2 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 rounded-lg">
                            <flux:icon name="users" class="size-5" />
                        </div>
                    </div>
                    <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-xl p-5 shadow-xs flex items-center justify-between hover:scale-[1.02] transition-transform">
                        <div>
                            <span class="text-xs font-semibold uppercase text-indigo-500 tracking-wider">{{ __('Customers') }}</span>
                            <h4 class="text-2xl font-black mt-1 text-neutral-800 dark:text-neutral-100">{{ $this->roleDistribution['customer'] }}</h4>
                        </div>
                        <div class="p-2 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 rounded-lg">
                            <flux:icon name="user" class="size-5" />
                        </div>
                    </div>
                </div>

                <!-- Grid: Permission Matrix & Role Management Table -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- User Role Auditor Table -->
                    <div class="lg:col-span-2 bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Auditable Accounts') }}</flux:heading>
                                <flux:subheading class="text-xs">{{ __('Change system roles and check linked properties.') }}</flux:subheading>
                            </div>
                            <div class="w-full sm:w-60 flex gap-2">
                                <flux:select wire:model.live="filterRole" size="sm" class="w-full">
                                    <option value="">{{ __('All Security Roles') }}</option>
                                    <option value="super_admin">{{ __('Super Admin') }}</option>
                                    <option value="manager">{{ __('Manager') }}</option>
                                    <option value="staff">{{ __('Staff') }}</option>
                                    <option value="customer">{{ __('Customer') }}</option>
                                </flux:select>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[600px] text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-neutral-100 dark:border-stone-800 text-sm">
                                        <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('User') }}</th>
                                        <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Security Role') }}</th>
                                        <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-right">{{ __('Switch Roles') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-50 dark:divide-stone-800/40">
                                    @forelse ($this->users as $u)
                                        <tr class="hover:bg-neutral-50/50 dark:hover:bg-stone-800/20 transition-colors">
                                            <td class="py-4">
                                                <div class="font-bold text-neutral-800 dark:text-neutral-200">{{ $u->name }}</div>
                                                <div class="text-xs text-neutral-500">{{ $u->email }}</div>
                                                @if ($u->restaurant)
                                                    <span class="text-[10px] bg-neutral-100 dark:bg-stone-800 px-2 py-0.5 rounded text-neutral-500 font-semibold mt-1 inline-block">
                                                        {{ $u->restaurant->name }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-4">
                                                @php
                                                    $r = $u->roles->pluck('name')->first() ?? 'customer';
                                                    $badgeColor = match($r) {
                                                        'super_admin' => 'rose',
                                                        'manager' => 'teal',
                                                        'staff' => 'amber',
                                                        default => 'indigo'
                                                    };
                                                @endphp
                                                <flux:badge color="{{ $badgeColor }}" class="font-extrabold uppercase text-[10px] tracking-wider px-2.5 py-0.5">
                                                    {{ str_replace('_', ' ', $r) }}
                                                </flux:badge>
                                            </td>
                                            <td class="py-4 text-right">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    @if ($r !== 'super_admin')
                                                        <flux:button size="xs" variant="{{ $r === 'manager' ? 'filled' : 'ghost' }}" wire:click="changeUserRole({{ $u->id }}, 'manager')" class="font-semibold cursor-pointer">
                                                            Manager
                                                        </flux:button>
                                                        <flux:button size="xs" variant="{{ $r === 'staff' ? 'filled' : 'ghost' }}" wire:click="changeUserRole({{ $u->id }}, 'staff')" class="font-semibold cursor-pointer">
                                                            Staff
                                                        </flux:button>
                                                        <flux:button size="xs" variant="{{ $r === 'customer' ? 'filled' : 'ghost' }}" wire:click="changeUserRole({{ $u->id }}, 'customer')" class="font-semibold cursor-pointer">
                                                            Customer
                                                        </flux:button>
                                                    @else
                                                        <span class="text-xs text-neutral-400 italic px-2">{{ __('Locked (Super Admin)') }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-8 text-center text-neutral-500">
                                                {{ __('No users found with the selected role.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="pt-4 border-t border-neutral-100 dark:border-stone-800">
                            {{ $this->users->links() }}
                        </div>
                    </div>

                    <!-- Permissions Security Matrix -->
                    <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-4 h-fit">
                        <div>
                            <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Permission Matrix') }}</flux:heading>
                            <flux:subheading class="text-xs">{{ __('Platform security policy maps showing feature actions allowed.') }}</flux:subheading>
                        </div>

                        <div class="space-y-4 text-sm">
                            <div class="border border-neutral-150 dark:border-stone-850 rounded-xl overflow-hidden">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="bg-neutral-50 dark:bg-stone-950/40 border-b border-neutral-150 dark:border-stone-850 text-neutral-500 font-bold">
                                            <th class="p-3">{{ __('App Capability') }}</th>
                                            <th class="p-3 text-center">Adm</th>
                                            <th class="p-3 text-center">Mgr</th>
                                            <th class="p-3 text-center">Stf</th>
                                            <th class="p-3 text-center">Cst</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-neutral-150 dark:divide-stone-850">
                                        <tr class="hover:bg-neutral-50/50">
                                            <td class="p-3 font-semibold text-neutral-700 dark:text-neutral-300">Provision Resto</td>
                                            <td class="p-3 text-center text-emerald-500">✓</td>
                                            <td class="p-3 text-center text-red-500">✗</td>
                                            <td class="p-3 text-center text-red-500">✗</td>
                                            <td class="p-3 text-center text-red-500">✗</td>
                                        </tr>
                                        <tr class="hover:bg-neutral-50/50">
                                            <td class="p-3 font-semibold text-neutral-700 dark:text-neutral-300">Bistro Console</td>
                                            <td class="p-3 text-center text-emerald-500">✓</td>
                                            <td class="p-3 text-center text-emerald-500">✓</td>
                                            <td class="p-3 text-center text-red-500">✗</td>
                                            <td class="p-3 text-center text-red-500">✗</td>
                                        </tr>
                                        <tr class="hover:bg-neutral-50/50">
                                            <td class="p-3 font-semibold text-neutral-700 dark:text-neutral-300">Manage Menu</td>
                                            <td class="p-3 text-center text-emerald-500">✓</td>
                                            <td class="p-3 text-center text-emerald-500">✓</td>
                                            <td class="p-3 text-center text-red-500">✗</td>
                                            <td class="p-3 text-center text-red-500">✗</td>
                                        </tr>
                                        <tr class="hover:bg-neutral-50/50">
                                            <td class="p-3 font-semibold text-neutral-700 dark:text-neutral-300">Attend Shifts</td>
                                            <td class="p-3 text-center text-emerald-500">✓</td>
                                            <td class="p-3 text-center text-emerald-500">✓</td>
                                            <td class="p-3 text-center text-emerald-500">✓</td>
                                            <td class="p-3 text-center text-red-500">✗</td>
                                        </tr>
                                        <tr class="hover:bg-neutral-50/50">
                                            <td class="p-3 font-semibold text-neutral-700 dark:text-neutral-300">Book Tables</td>
                                            <td class="p-3 text-center text-red-500">✗</td>
                                            <td class="p-3 text-center text-red-500">✗</td>
                                            <td class="p-3 text-center text-red-500">✗</td>
                                            <td class="p-3 text-center text-emerald-500">✓</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-[11px] text-neutral-500 bg-neutral-50 dark:bg-stone-950/10 p-3 rounded-lg flex gap-2">
                                <flux:icon name="exclamation-circle" class="size-4 shrink-0 text-indigo-500" />
                                <span>{{ __('Permissions map is automatically configured by Spatie Middleware roles. Modifying user roles changes their permission mapping immediately.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @elseif ($tab === 'requests')
            <!-- Pending Manager Requests Tab View -->
            <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-6">
                <div class="flex items-center justify-between border-b border-neutral-100 dark:border-stone-800 pb-4">
                    <div>
                        <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Eatery Approvals Board') }}</flux:heading>
                        <flux:subheading class="text-xs">{{ __('Review requests from restaurant owners waiting to onboard.') }}</flux:subheading>
                    </div>
                    <flux:badge color="amber" class="font-bold px-3 py-0.5">{{ $this->pendingRequests->count() }} {{ __('Pending') }}</flux:badge>
                </div>

                @if ($this->pendingRequests->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[750px] text-left border-collapse">
                            <thead>
                                <tr class="border-b border-neutral-150 dark:border-stone-850 text-sm">
                                    <th class="py-3 px-4 font-bold text-neutral-500 dark:text-neutral-400">{{ __('Applicant Manager') }}</th>
                                    <th class="py-3 px-4 font-bold text-neutral-500 dark:text-neutral-400">{{ __('Restaurant details') }}</th>
                                    <th class="py-3 px-4 font-bold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Stripe Checkout') }}</th>
                                    <th class="py-3 px-4 font-bold text-neutral-500 dark:text-neutral-400 text-right">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-50 dark:divide-stone-800/40">
                                @foreach ($this->pendingRequests as $req)
                                    <tr class="hover:bg-neutral-50/50 dark:hover:bg-stone-800/20 transition-colors">
                                        <td class="py-4 px-4">
                                            <div class="font-bold text-neutral-800 dark:text-neutral-200">{{ $req->name }}</div>
                                            <div class="text-xs text-neutral-500">{{ $req->email }}</div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $req->restaurant_name }}</div>
                                            <div class="text-xs text-neutral-500">{{ $req->restaurant_phone }}</div>
                                            <div class="text-xs text-neutral-400 truncate max-w-xs">{{ $req->restaurant_address }}</div>
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <flux:badge color="green" class="font-bold">Paid ₹{{ number_format($req->payment_amount, 0) }}</flux:badge>
                                        </td>
                                        <td class="py-4 px-4 text-right">
                                            <flux:button size="sm" variant="primary" class="bg-amber-600 hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-600 text-white font-bold py-1.5 px-4 rounded-xl border-none" wire:click="approveManagerRequest({{ $req->id }})">
                                                {{ __('Approve & Provision') }}
                                            </flux:button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-12 flex flex-col items-center justify-center text-center space-y-4">
                        <div class="p-4 bg-neutral-50 dark:bg-stone-950/20 text-neutral-400 rounded-full">
                            <flux:icon name="check-circle" class="size-10 text-neutral-400" />
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-neutral-800 dark:text-neutral-200 text-lg">{{ __('Inbox Cleared!') }}</h4>
                            <p class="text-sm text-neutral-500 max-w-sm">{{ __('There are no pending manager requests or outstanding restaurant approvals at this time.') }}</p>
                        </div>
                    </div>
                @endif
            </div>

        @elseif ($tab === 'audits')
            <!-- Audit Logs Tab View -->
            <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-6">
                <div>
                    <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Platform Reservation Audits') }}</flux:heading>
                    <flux:subheading class="text-xs">{{ __('Comprehensive list of billing logs and reservations on the SaaS platform.') }}</flux:subheading>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[750px] text-left border-collapse">
                        <thead>
                            <tr class="border-b border-neutral-100 dark:border-stone-800 text-sm">
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Customer') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Eatery Point') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Scheduled Date') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Payment amount') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-right">{{ __('Booking Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-50 dark:divide-stone-800/40">
                            @forelse ($this->bookings as $b)
                                <tr class="hover:bg-neutral-50/50 dark:hover:bg-stone-800/20 transition-colors">
                                    <td class="py-4">
                                        <div class="font-bold text-neutral-800 dark:text-neutral-200">{{ $b->user->name }}</div>
                                        <div class="text-xs text-neutral-500">{{ $b->user->email }}</div>
                                    </td>
                                    <td class="py-4 text-sm text-neutral-600 dark:text-neutral-400 font-semibold">
                                        {{ $b->restaurant->name }}
                                    </td>
                                    <td class="py-4 text-center text-sm text-neutral-800 dark:text-neutral-200 font-medium">
                                        <div>{{ $b->booking_date->format('M d, Y') }}</div>
                                        <div class="text-xs text-neutral-500">{{ \Carbon\Carbon::parse($b->booking_time)->format('h:i A') }}</div>
                                    </td>
                                    <td class="py-4 text-center">
                                        <div class="font-extrabold text-green-600 dark:text-green-400">₹{{ number_format($b->payment_amount, 2) }}</div>
                                        <div class="text-[10px] text-neutral-400 uppercase tracking-wide font-semibold">{{ str_replace('_', ' ', $b->payment_status) }}</div>
                                    </td>
                                    <td class="py-4 text-right">
                                        @php
                                            $bColor = match($b->status) {
                                                'confirmed' => 'green',
                                                'completed' => 'teal',
                                                'pending' => 'amber',
                                                default => 'red'
                                            };
                                        @endphp
                                        <flux:badge size="sm" color="{{ $bColor }}" class="font-bold uppercase text-[10px] tracking-wide">
                                            {{ $b->status }}
                                        </flux:badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-neutral-500">
                                        {{ __('No platform audit entries found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-4 border-t border-neutral-100 dark:border-stone-800">
                    {{ $this->bookings->links() }}
                </div>
            </div>
        @elseif ($tab === 'logs')
            <!-- Live Logs Tab View -->
            <div class="bg-stone-950 dark:bg-stone-950 border border-stone-800 rounded-2xl shadow-xl font-mono overflow-hidden">
                <!-- Terminal Header -->
                <div class="bg-stone-900 border-b border-stone-800 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-1.5">
                            <span class="size-3 rounded-full bg-red-500/80"></span>
                            <span class="size-3 rounded-full bg-amber-500/80"></span>
                            <span class="size-3 rounded-full bg-green-500/80"></span>
                        </div>
                        <div class="h-4 w-px bg-stone-800"></div>
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                            </span>
                            <span class="text-xs text-stone-400 font-bold tracking-wide uppercase">{{ __('Live Log Monitor') }}</span>
                        </div>
                    </div>
                    <div>
                        <flux:button variant="ghost" size="xs" wire:click="clearLogs" class="text-stone-400 hover:text-white border border-stone-800 hover:bg-stone-850 font-bold px-3 py-1 rounded-lg cursor-pointer transition-colors">
                            {{ __('Clear Logs') }}
                        </flux:button>
                    </div>
                </div>

                <!-- Terminal Console Body -->
                <div wire:poll.1s class="p-6 max-h-[600px] overflow-y-auto space-y-1.5 scrollbar-thin scrollbar-thumb-stone-800 scrollbar-track-transparent">
                    <!-- Terminal Input Prompt -->
                    <div class="text-stone-400 select-none pb-2 text-xs">
                        <span class="text-emerald-400 font-bold">akash@zayka-dining</span>:<span class="text-sky-400 font-bold">~/resto-app</span>$ tail -n 50 -f storage/logs/laravel.log
                    </div>

                    @forelse ($this->systemLogs as $log)
                        @php
                            $lvl = strtoupper($log['level']);
                            $levelColor = match($lvl) {
                                'ERROR', 'FATAL', 'CRITICAL', 'EMERGENCY', 'ALERT' => 'text-red-500 font-black',
                                'WARNING' => 'text-amber-500 font-bold',
                                default => 'text-emerald-500 font-semibold'
                            };
                        @endphp
                        <div class="text-xs leading-normal whitespace-pre-wrap break-all font-mono">
                            <!-- Time stamp -->
                            <span class="text-stone-500 select-none">[{{ $log['timestamp'] }}]</span>
                            <!-- Env and Level prefix -->
                            <span class="{{ $levelColor }}">{{ $log['env'] }}.{{ $lvl }}:</span>
                            <!-- Message -->
                            <span class="text-stone-200 font-medium">{{ $log['full_message'] }}</span>
                        </div>
                    @empty
                        <div class="py-16 text-center space-y-3">
                            <flux:icon name="command-line" class="size-8 text-stone-700 mx-auto" />
                            <div class="text-stone-500 text-sm font-bold">{{ __('Console clear. No new system log entries.') }}</div>
                        </div>
                    @endforelse

                    @if (!empty($this->systemLogs))
                        <!-- Blinking Cursor Prompt Line -->
                        <div class="flex items-center gap-1.5 text-xs select-none pt-2 border-t border-stone-900/40">
                            <span class="text-stone-500">akash@zayka-dining:~/resto-app$</span>
                            <span class="inline-block w-2 h-4 bg-emerald-400 animate-pulse"></span>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
