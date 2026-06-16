<?php

use Livewire\Component;
use App\Models\Table;
use App\Models\User;
use App\Models\Booking;
use App\Models\Attendance;
use App\Models\Restaurant;
use App\Models\MenuItem;
use App\Models\Blog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Flux\Flux;

new class extends Component {
    use WithPagination;
    use WithFileUploads;

    #[Url(keep: true)]
    public string $tab = 'dashboard';

    public $resto_image;

    public ?string $logsClearedAt = null;

    // Search queries
    public string $searchBooking = '';
    public string $searchMenu = '';

    // Edit Restaurant Profile fields
    public string $resto_name = '';
    public string $resto_email = '';
    public string $resto_phone = '';
    public string $resto_address = '';

    // Create Table fields
    public string $table_name = '';
    public int $table_capacity = 2;

    // Create Staff fields
    public string $staff_name = '';
    public string $staff_email = '';

    // Create Menu Item fields
    public string $menu_name = '';
    public string $menu_description = '';
    public string $menu_price = '';
    public string $menu_category = 'Main Course';

    // Create Blog fields
    public string $blog_title = '';
    public string $blog_content = '';

    public function mount(): void
    {
        $restaurant = Auth::user()->restaurant;
        if ($restaurant) {
            $this->resto_name = $restaurant->name;
            $this->resto_email = $restaurant->email ?? '';
            $this->resto_phone = $restaurant->phone ?? '';
            $this->resto_address = $restaurant->address ?? '';
        }
    }

    public function updatingSearchBooking(): void
    {
        $this->resetPage('bookingsPage');
    }

    public function updatingSearchMenu(): void
    {
        $this->resetPage('menuPage');
    }

    public function updateProfile(): void
    {
        $this->validate([
            'resto_name' => 'required|string|max:255',
            'resto_email' => 'required|email|max:255',
            'resto_phone' => 'required|string',
            'resto_address' => 'required|string',
            'resto_image' => 'nullable|image|max:2048',
        ]);

        $restaurant = Auth::user()->restaurant;
        $data = [
            'name' => $this->resto_name,
            'email' => $this->resto_email,
            'phone' => $this->resto_phone,
            'address' => $this->resto_address,
        ];

        if ($this->resto_image) {
            $path = $this->resto_image->store('restaurants', 'public');
            $data['image_path'] = $path;
            Log::info("[Restaurant ID: {$restaurant->id}] Manager uploaded restaurant cover image: {$path}");
        }

        $restaurant->update($data);

        Log::info("[Restaurant ID: {$restaurant->id}] Manager updated restaurant details: {$restaurant->name}.");

        Flux::toast(variant: 'success', text: __('Restaurant profile updated.'));
        $this->reset('resto_image');
    }

    public function createTable(): void
    {
        $this->validate([
            'table_name' => 'required|string|max:255',
            'table_capacity' => 'required|integer|min:1|max:20',
        ]);

        $restaurant = Auth::user()->restaurant;
        $table = $restaurant->tables()->create([
            'name' => $this->table_name,
            'capacity' => $this->table_capacity,
            'status' => 'available',
        ]);

        Log::info("[Restaurant ID: {$restaurant->id}] Manager created seating table: {$table->name} (Capacity: {$table->capacity}).");

        Flux::toast(variant: 'success', text: __('Table created.'));
        $this->reset(['table_name', 'table_capacity']);
        
        // Refresh computed
        unset($this->tablesList);
    }

    public function toggleTableStatus(int $id): void
    {
        $table = Table::findOrFail($id);
        $table->update([
            'status' => $table->status === 'available' ? 'out_of_order' : 'available'
        ]);

        Log::info("[Restaurant ID: {$table->restaurant_id}] Manager toggled seating table '{$table->name}' (ID: {$id}) status to: {$table->status}.");

        Flux::toast(variant: 'success', text: __('Table status toggled.'));
        
        // Refresh computed
        unset($this->tablesList);
    }

    public function addStaff(): void
    {
        $this->validate([
            'staff_name' => 'required|string|max:255',
            'staff_email' => 'required|email|unique:users,email',
        ]);

        $restaurant = Auth::user()->restaurant;

        // Create User
        $user = User::create([
            'name' => $this->staff_name,
            'email' => $this->staff_email,
            'restaurant_id' => $restaurant->id,
            'password' => Hash::make(Str::random(32)),
        ]);
        $user->assignRole('staff');

        Log::info("[Restaurant ID: {$restaurant->id}] Manager recruited new staff member: {$user->name} (ID: {$user->id}, Email: {$user->email}).");

        Flux::toast(variant: 'success', text: __('Staff member registered. They can log in via OTP.'));
        $this->reset(['staff_name', 'staff_email']);
        
        // Refresh computed
        unset($this->staffList);
    }

    public function updateBookingStatus(int $id, string $status): void
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => $status]);

        Log::info("[Restaurant ID: {$booking->restaurant_id}] Manager updated booking ID {$id} status to: {$status}.");

        // Send Notification
        try {
            $booking->user->notify(new \App\Notifications\BookingStatusNotification($booking, $status));
        } catch (\Exception $e) {
            logger()->error('Notification failed: ' . $e->getMessage());
        }

        Flux::toast(variant: 'success', text: __('Booking status updated.'));
        
        // Refresh computed
        unset($this->bookings);
        unset($this->todayBookings);
    }

    public function createMenuItem(): void
    {
        $this->validate([
            'menu_name' => 'required|string|max:255',
            'menu_description' => 'nullable|string',
            'menu_price' => 'required|numeric|min:0',
            'menu_category' => 'required|string|max:255',
        ]);

        $restaurant = Auth::user()->restaurant;
        $item = $restaurant->menuItems()->create([
            'name' => $this->menu_name,
            'description' => $this->menu_description,
            'price' => (float)$this->menu_price,
            'category' => $this->menu_category,
            'is_available' => true,
        ]);

        Log::info("[Restaurant ID: {$restaurant->id}] Manager created menu item: {$item->name} (Category: {$item->category}, Price: ₹{$item->price}).");

        Flux::toast(variant: 'success', text: __('Menu item added.'));
        $this->reset(['menu_name', 'menu_description', 'menu_price', 'menu_category']);
        
        // Refresh computed
        unset($this->menuItemsList);
    }

    public function toggleMenuItemAvailability(int $id): void
    {
        $item = MenuItem::findOrFail($id);
        $item->update([
            'is_available' => !$item->is_available
        ]);

        Log::info("[Restaurant ID: {$item->restaurant_id}] Manager toggled availability of menu item '{$item->name}' (ID: {$id}) to: " . ($item->is_available ? 'In Stock' : 'Out of Stock') . ".");

        Flux::toast(variant: 'success', text: __('Menu item availability toggled.'));
        
        // Refresh computed
        unset($this->menuItemsList);
    }

    public function deleteMenuItem(int $id): void
    {
        $item = MenuItem::findOrFail($id);
        $item->delete();

        Log::info("[Restaurant ID: {$item->restaurant_id}] Manager deleted menu item: '{$item->name}' (ID: {$id}).");

        Flux::toast(variant: 'success', text: __('Menu item deleted.'));
        
        // Refresh computed
        unset($this->menuItemsList);
    }

    public function createBlog(): void
    {
        $this->validate([
            'blog_title' => 'required|string|max:255',
            'blog_content' => 'required|string',
        ]);

        $restaurant = Auth::user()->restaurant;
        $blog = $restaurant->blogs()->create([
            'title' => $this->blog_title,
            'slug' => Str::slug($this->blog_title),
            'content' => $this->blog_content,
        ]);

        Log::info("[Restaurant ID: {$restaurant->id}] Manager published blog post: '{$blog->title}' (ID: {$blog->id}).");

        Flux::toast(variant: 'success', text: __('Blog post published.'));
        $this->reset(['blog_title', 'blog_content']);
        
        // Refresh computed
        unset($this->blogsList);
    }

    public function deleteBlog(int $id): void
    {
        $blog = Blog::findOrFail($id);
        $blog->delete();

        Log::info("[Restaurant ID: {$blog->restaurant_id}] Manager deleted blog post: '{$blog->title}' (ID: {$id}).");

        Flux::toast(variant: 'success', text: __('Blog post deleted.'));
        
        // Refresh computed
        unset($this->blogsList);
    }

    #[Computed]
    public function stats(): array
    {
        $restaurantId = Auth::user()->restaurant_id;

        return [
            'monthly_revenue' => Booking::where('restaurant_id', $restaurantId)
                ->whereIn('payment_status', ['paid_full', 'paid_advance'])
                ->sum('payment_amount'),
            'total_tables' => Table::where('restaurant_id', $restaurantId)->count(),
            'total_staff' => User::where('restaurant_id', $restaurantId)->role('staff')->count(),
            'pending_bookings' => Booking::where('restaurant_id', $restaurantId)->where('status', 'pending')->count(),
        ];
    }

    #[Computed]
    public function bookings(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $restaurantId = Auth::user()->restaurant_id;
        return Booking::where('restaurant_id', $restaurantId)
            ->with(['user', 'table'])
            ->when($this->searchBooking, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->searchBooking . '%')
                      ->orWhere('email', 'like', '%' . $this->searchBooking . '%');
                });
            })
            ->latest()
            ->paginate(10, ['*'], 'bookingsPage');
    }

    #[Computed]
    public function todayBookings(): \Illuminate\Database\Eloquent\Collection
    {
        $restaurantId = Auth::user()->restaurant_id;
        return Booking::where('restaurant_id', $restaurantId)
            ->with(['user', 'table'])
            ->whereDate('booking_date', now()->toDateString())
            ->latest()
            ->get();
    }

    #[Computed]
    public function tablesList(): \Illuminate\Database\Eloquent\Collection
    {
        $restaurantId = Auth::user()->restaurant_id;
        return Table::where('restaurant_id', $restaurantId)->orderBy('name')->get();
    }

    #[Computed]
    public function staffList(): \Illuminate\Database\Eloquent\Collection
    {
        $restaurantId = Auth::user()->restaurant_id;
        return User::where('restaurant_id', $restaurantId)->role('staff')->orderBy('name')->get();
    }

    #[Computed]
    public function attendanceLogs(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $restaurantId = Auth::user()->restaurant_id;
        return Attendance::where('restaurant_id', $restaurantId)
            ->with('user')
            ->latest()
            ->paginate(10, ['*'], 'attendancePage');
    }

    #[Computed]
    public function menuItemsList(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $restaurantId = Auth::user()->restaurant_id;
        return MenuItem::where('restaurant_id', $restaurantId)
            ->when($this->searchMenu, function ($query) {
                $query->where('name', 'like', '%' . $this->searchMenu . '%')
                      ->orWhere('category', 'like', '%' . $this->searchMenu . '%');
            })
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(10, ['*'], 'menuPage');
    }

    #[Computed]
    public function blogsList(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $restaurantId = Auth::user()->restaurant_id;
        return Blog::where('restaurant_id', $restaurantId)
            ->latest()
            ->paginate(5, ['*'], 'blogsPage');
    }

    #[Computed]
    public function restaurantLogs(): array
    {
        $restaurantId = Auth::user()->restaurant_id;
        if (!$restaurantId) {
            return [];
        }

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

        // Filter logs for this specific restaurant ID
        $filtered = array_filter($entries, function ($entry) use ($restaurantId) {
            return str_contains($entry['message'], "[Restaurant ID: {$restaurantId}]");
        });

        // Filter out logs before the logsClearedAt timestamp
        if ($this->logsClearedAt) {
            $filtered = array_filter($filtered, function ($entry) {
                return $entry['timestamp'] > $this->logsClearedAt;
            });
        }

        return array_slice(array_reverse($filtered), 0, 50);
    }

    public function clearLogs(): void
    {
        $restaurantId = Auth::user()->restaurant_id;
        $this->logsClearedAt = now()->toDateTimeString();
        
        Log::info("[Restaurant ID: {$restaurantId}] Logs cleared by manager.");
        Flux::toast(variant: 'success', text: __('Activity logs cleared successfully.'));
        
        unset($this->restaurantLogs);
    }
}; ?>

    <div class="space-y-8 p-6 max-w-7xl mx-auto transition-all duration-300">
        <!-- Dashboard Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-neutral-100 dark:border-stone-800 pb-6">
            <div>
                @if ($tab === 'dashboard')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-indigo-600 to-violet-600 dark:from-indigo-400 dark:to-violet-400 bg-clip-text text-transparent">{{ __('Bistro Overview') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Monitor live reservations, active dining floor status, and stats for ' . Auth::user()->restaurant->name) }}</flux:subheading>
                @elseif ($tab === 'reservations')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-sky-600 to-indigo-600 dark:from-sky-400 dark:to-indigo-400 bg-clip-text text-transparent">{{ __('Reservations Console') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Approve and manage bookings, track payments, and review dining history') }}</flux:subheading>
                @elseif ($tab === 'menu')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-amber-600 to-orange-600 dark:from-amber-400 dark:to-orange-400 bg-clip-text text-transparent">{{ __('Royal Menu Management') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Publish, organize, and toggle availability of your restaurant dishes') }}</flux:subheading>
                @elseif ($tab === 'blogs')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-fuchsia-600 to-pink-600 dark:from-fuchsia-400 dark:to-pink-400 bg-clip-text text-transparent">{{ __('Culinary Blogs') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Share culinary updates, spice guides, and recipes with customers') }}</flux:subheading>
                @elseif ($tab === 'tables')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-teal-600 to-emerald-600 dark:from-teal-400 dark:to-emerald-400 bg-clip-text text-transparent">{{ __('Floor Tables Configuration') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Configure and status-toggle your physical seating dining tables') }}</flux:subheading>
                @elseif ($tab === 'staff')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-violet-600 to-indigo-600 dark:from-violet-400 dark:to-indigo-400 bg-clip-text text-transparent">{{ __('Staff Roster & Shifts') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Recruit staff and audit shifts, check-in and check-out logs') }}</flux:subheading>
                @elseif ($tab === 'profile')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-rose-600 to-pink-600 dark:from-rose-400 dark:to-pink-400 bg-clip-text text-transparent">{{ __('Restaurant Details') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Update public contact information, emails, and address locations') }}</flux:subheading>
                @elseif ($tab === 'logs')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-slate-600 to-zinc-600 dark:from-slate-400 dark:to-zinc-400 bg-clip-text text-transparent">{{ __('Bistro Live Logs') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Inspect recent logs and real-time operational updates for ' . Auth::user()->restaurant->name) }}</flux:subheading>
                @endif
            </div>
            <div>
                <flux:badge color="indigo" class="text-sm font-bold px-3 py-1 shadow-sm uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-900/50">{{ __('Manager') }}</flux:badge>
            </div>
        </div>

        @if ($tab === 'dashboard')
            <!-- KPI Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Revenue Card -->
                <div class="relative overflow-hidden bg-gradient-to-br from-green-500/5 via-white to-white dark:from-green-500/10 dark:via-stone-900 dark:to-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-300 group cursor-pointer" wire:click="$set('tab', 'reservations')">
                    <div class="absolute right-0 top-0 size-24 bg-green-500/5 dark:bg-green-500/10 rounded-bl-full pointer-events-none transition-all duration-300 group-hover:scale-110"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:text size="sm" class="text-neutral-500 dark:text-neutral-400 font-medium tracking-wide uppercase">{{ __('Monthly Revenue') }}</flux:text>
                            <flux:heading size="xl" class="mt-2 font-black text-green-600 dark:text-green-400">₹{{ number_format($this->stats['monthly_revenue'], 2) }}</flux:heading>
                        </div>
                        <div class="p-3 bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 rounded-xl">
                            <flux:icon name="currency-dollar" class="size-6" />
                        </div>
                    </div>
                </div>

                <!-- Tables Card -->
                <div class="relative overflow-hidden bg-gradient-to-br from-indigo-500/5 via-white to-white dark:from-indigo-500/10 dark:via-stone-900 dark:to-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-300 group cursor-pointer" wire:click="$set('tab', 'tables')">
                    <div class="absolute right-0 top-0 size-24 bg-indigo-500/5 dark:bg-indigo-500/10 rounded-bl-full pointer-events-none transition-all duration-300 group-hover:scale-110"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:text size="sm" class="text-neutral-500 dark:text-neutral-400 font-medium tracking-wide uppercase">{{ __('Dining Tables') }}</flux:text>
                            <flux:heading size="xl" class="mt-2 font-black text-neutral-800 dark:text-neutral-100">{{ $this->stats['total_tables'] }}</flux:heading>
                        </div>
                        <div class="p-3 bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-xl">
                            <flux:icon name="table-cells" class="size-6" />
                        </div>
                    </div>
                </div>

                <!-- Staff Card -->
                <div class="relative overflow-hidden bg-gradient-to-br from-violet-500/5 via-white to-white dark:from-violet-500/10 dark:via-stone-900 dark:to-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-300 group cursor-pointer" wire:click="$set('tab', 'staff')">
                    <div class="absolute right-0 top-0 size-24 bg-violet-500/5 dark:bg-violet-500/10 rounded-bl-full pointer-events-none transition-all duration-300 group-hover:scale-110"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:text size="sm" class="text-neutral-500 dark:text-neutral-400 font-medium tracking-wide uppercase">{{ __('Staff Roster') }}</flux:text>
                            <flux:heading size="xl" class="mt-2 font-black text-neutral-800 dark:text-neutral-100">{{ $this->stats['total_staff'] }}</flux:heading>
                        </div>
                        <div class="p-3 bg-violet-500/10 dark:bg-violet-500/20 text-violet-600 dark:text-violet-400 rounded-xl">
                            <flux:icon name="users" class="size-6" />
                        </div>
                    </div>
                </div>

                <!-- Bookings Card -->
                <div class="relative overflow-hidden bg-gradient-to-br from-amber-500/5 via-white to-white dark:from-amber-500/10 dark:via-stone-900 dark:to-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-300 group cursor-pointer" wire:click="$set('tab', 'reservations')">
                    <div class="absolute right-0 top-0 size-24 bg-amber-500/5 dark:bg-amber-500/10 rounded-bl-full pointer-events-none transition-all duration-300 group-hover:scale-110"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:text size="sm" class="text-neutral-500 dark:text-neutral-400 font-medium tracking-wide uppercase">{{ __('Pending Bookings') }}</flux:text>
                            <flux:heading size="xl" class="mt-2 font-black text-neutral-800 dark:text-neutral-100">{{ $this->stats['pending_bookings'] }}</flux:heading>
                        </div>
                        <div class="p-3 bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-xl">
                            <flux:icon name="clock" class="size-6" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Double Columns Dashboard Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left: Today's Reservations Console -->
                <div class="lg:col-span-2 bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __("Today's Reservations") }}</flux:heading>
                            <flux:subheading class="text-xs">{{ __('Live bookings scheduled for today') }}</flux:subheading>
                        </div>
                        <flux:button variant="ghost" size="xs" wire:click="$set('tab', 'reservations')" class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ __('View All') }} &rarr;</flux:button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[600px] text-left border-collapse">
                            <thead>
                                <tr class="border-b border-neutral-100 dark:border-stone-800 text-xs">
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Customer') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Guests & Table') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Scheduled Time') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-50 dark:divide-stone-800/40">
                                @forelse ($this->todayBookings as $booking)
                                    <tr class="hover:bg-neutral-50/50 dark:hover:bg-stone-800/20 transition-colors">
                                        <td class="py-4">
                                            <div class="font-bold text-neutral-800 dark:text-neutral-200 text-sm">{{ $booking->user->name }}</div>
                                            <div class="text-[10px] text-neutral-400 font-mono">{{ $booking->user->email }}</div>
                                        </td>
                                        <td class="py-4 text-center">
                                            <div class="text-sm font-bold text-neutral-800 dark:text-neutral-200">{{ $booking->guest_count }} {{ __('Guests') }}</div>
                                            <div class="text-[10px] text-neutral-400">{{ __('Table') }}: {{ $booking->table ? $booking->table->name : 'N/A' }}</div>
                                        </td>
                                        <td class="py-4 text-center">
                                            <div class="text-sm font-bold text-neutral-800 dark:text-neutral-200">{{ \Carbon\Carbon::parse($booking->booking_time)->format('h:i A') }}</div>
                                            <div class="flex justify-center mt-1">
                                                <flux:badge color="{{ $booking->status === 'confirmed' ? 'green' : ($booking->status === 'pending' ? 'amber' : 'red') }}" size="xs" class="font-bold tracking-wide uppercase text-[8px] px-1.5 py-0.5">
                                                    {{ $booking->status }}
                                                </flux:badge>
                                            </div>
                                        </td>
                                        <td class="py-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if ($booking->status === 'pending')
                                                    <flux:button size="xs" variant="primary" class="bg-green-600 hover:bg-green-700 text-white font-bold" wire:click="updateBookingStatus({{ $booking->id }}, 'confirmed')">{{ __('Approve') }}</flux:button>
                                                @elseif ($booking->status === 'confirmed')
                                                    <flux:button size="xs" variant="ghost" class="text-xs font-semibold" wire:click="updateBookingStatus({{ $booking->id }}, 'completed')">{{ __('Complete') }}</flux:button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-12 text-center text-neutral-500 text-sm">
                                            {{ __('No reservations scheduled for today.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right: Seating Floor Quick View -->
                <div class="space-y-6">
                    <!-- Tables List card -->
                    <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-4">
                        <div class="flex items-center justify-between">
                            <flux:heading size="md" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Dining Floor Status') }}</flux:heading>
                            <flux:button variant="ghost" size="xs" wire:click="$set('tab', 'tables')" class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ __('Manage') }} &rarr;</flux:button>
                        </div>
                        <div class="divide-y divide-neutral-50 dark:divide-stone-800/40 max-h-56 overflow-y-auto">
                            @forelse ($this->tablesList->take(5) as $t)
                                <div class="flex items-center justify-between py-3">
                                    <div class="flex items-center gap-2">
                                        <flux:icon name="table-cells" class="size-4 text-neutral-400" />
                                        <span class="font-bold text-neutral-700 dark:text-stone-300 text-sm">{{ $t->name }}</span>
                                        <span class="text-xs text-neutral-400 font-mono">({{ $t->capacity }} {{ __('Seats') }})</span>
                                    </div>
                                    <flux:badge size="xs" color="{{ $t->status === 'available' ? 'green' : 'red' }}" class="font-bold tracking-wide uppercase text-[8px]">
                                        {{ $t->status }}
                                    </flux:badge>
                                </div>
                            @empty
                                <div class="text-center py-6 text-xs text-neutral-400">{{ __('No tables registered.') }}</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Staff on Duty -->
                    <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-4">
                        <div class="flex items-center justify-between">
                            <flux:heading size="md" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('On-Duty Staff') }}</flux:heading>
                            <flux:button variant="ghost" size="xs" wire:click="$set('tab', 'staff')" class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ __('Roster') }} &rarr;</flux:button>
                        </div>
                        <div class="divide-y divide-neutral-50 dark:divide-stone-800/40">
                            @forelse ($this->staffList->take(5) as $s)
                                <div class="flex items-center justify-between py-3">
                                    <div class="flex items-center gap-2.5">
                                        <flux:avatar :name="$s->name" initials="{{ $s->initials() }}" size="xs" />
                                        <div>
                                            <div class="font-bold text-neutral-700 dark:text-stone-300 text-sm">{{ $s->name }}</div>
                                            <div class="text-[9px] text-neutral-400 font-mono">{{ $s->email }}</div>
                                        </div>
                                    </div>
                                    <flux:badge size="xs" color="indigo" class="font-black text-[8px] uppercase tracking-wide">Staff</flux:badge>
                                </div>
                            @empty
                                <div class="text-center py-6 text-xs text-neutral-400">{{ __('No staff registered.') }}</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        @elseif ($tab === 'reservations')
            <!-- Reservations full list -->
            <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('All Reservations') }}</flux:heading>
                        <flux:subheading class="text-xs">{{ __('Manage reservation requests and audit client visits') }}</flux:subheading>
                    </div>
                    <!-- Search input -->
                    <div class="w-full sm:w-72">
                        <flux:input wire:model.live.debounce.150ms="searchBooking" icon="magnifying-glass" placeholder="Search customer or email..." />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px] text-left border-collapse">
                        <thead>
                            <tr class="border-b border-neutral-100 dark:border-stone-800 text-sm">
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Customer') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Reservation Details') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Scheduled Date') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Payments') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Status') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-50 dark:divide-stone-800/40">
                            @forelse ($this->bookings as $booking)
                                <tr class="hover:bg-neutral-50/50 dark:hover:bg-stone-800/20 transition-colors">
                                    <td class="py-4">
                                        <div class="font-bold text-neutral-800 dark:text-neutral-200">{{ $booking->user->name }}</div>
                                        <div class="text-xs text-neutral-500 font-mono">{{ $booking->user->email }}</div>
                                    </td>
                                    <td class="py-4 text-sm text-neutral-600 dark:text-neutral-400 font-medium">
                                        <div>{{ $booking->guest_count }} {{ __('Guests') }}</div>
                                        <div class="text-xs text-neutral-500">{{ __('Table') }}: {{ $booking->table ? $booking->table->name : 'N/A' }}</div>
                                    </td>
                                    <td class="py-4 text-center text-sm text-neutral-800 dark:text-neutral-200 font-bold">
                                        <div>{{ $booking->booking_date->format('M d, Y') }}</div>
                                        <div class="text-xs text-neutral-500 font-mono">{{ \Carbon\Carbon::parse($booking->booking_time)->format('h:i A') }}</div>
                                    </td>
                                    <td class="py-4 text-center">
                                        <div class="font-extrabold text-green-600 dark:text-green-400">₹{{ number_format($booking->payment_amount, 2) }}</div>
                                        <div class="flex justify-center mt-1">
                                            <flux:badge color="{{ str_contains($booking->payment_status, 'paid') ? 'green' : 'amber' }}" size="xs" class="font-bold tracking-wide uppercase text-[8px] px-1.5">
                                                {{ str_replace('_', ' ', $booking->payment_status) }}
                                            </flux:badge>
                                        </div>
                                    </td>
                                    <td class="py-4 text-center">
                                        <div class="flex justify-center">
                                            <flux:badge color="{{ $booking->status === 'confirmed' ? 'green' : ($booking->status === 'pending' ? 'amber' : 'red') }}" class="font-bold uppercase tracking-wide text-[9px] px-2">
                                                {{ $booking->status }}
                                            </flux:badge>
                                        </div>
                                    </td>
                                    <td class="py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if ($booking->status === 'pending')
                                                <flux:button size="xs" variant="primary" class="bg-green-600 hover:bg-green-700 text-white font-bold py-1 px-3.5 rounded-lg border-none animate-bounce" wire:click="updateBookingStatus({{ $booking->id }}, 'confirmed')">{{ __('Approve') }}</flux:button>
                                                <flux:button size="xs" variant="danger" class="font-bold py-1 px-3.5 rounded-lg border-none" wire:click="updateBookingStatus({{ $booking->id }}, 'rejected')">{{ __('Reject') }}</flux:button>
                                            @elseif ($booking->status === 'confirmed')
                                                <flux:button size="xs" variant="ghost" class="text-xs font-semibold py-1 px-3 rounded-lg border border-stone-200 dark:border-stone-800" wire:click="updateBookingStatus({{ $booking->id }}, 'completed')">{{ __('Mark Completed') }}</flux:button>
                                                <flux:button size="xs" variant="danger" class="font-bold py-1 px-3 rounded-lg border-none" wire:click="updateBookingStatus({{ $booking->id }}, 'cancelled')">{{ __('Cancel') }}</flux:button>
                                            @endif
                                            @if (str_contains($booking->payment_status, 'paid'))
                                                <flux:button size="xs" variant="ghost" icon="document-text" class="border border-stone-200 dark:border-stone-800" as="a" href="{{ route('booking.invoice', ['id' => $booking->id]) }}" target="_blank" />
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-neutral-500">
                                        {{ __('No reservations found matching search query.') }}
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

        @elseif ($tab === 'menu')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Add Menu Item form (Left Column) -->
                <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-4 self-start">
                    <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Add Dish') }}</flux:heading>
                    <flux:subheading class="text-xs">{{ __('Add culinary points to the dynamic customer storefront menu') }}</flux:subheading>

                    <form wire:submit="createMenuItem" class="space-y-4 pt-2">
                        <flux:input wire:model="menu_name" :label="__('Item Name')" type="text" placeholder="Paneer Butter Masala" required />
                        <flux:input wire:model="menu_price" :label="__('Price (₹)')" type="number" step="0.01" min="0" placeholder="320" required />
                        <flux:select wire:model="menu_category" :label="__('Category')">
                            <option value="Starters">{{ __('Starters') }}</option>
                            <option value="Main Course">{{ __('Main Course') }}</option>
                            <option value="Desserts">{{ __('Desserts') }}</option>
                            <option value="Beverages">{{ __('Beverages') }}</option>
                        </flux:select>
                        <flux:textarea wire:model="menu_description" :label="__('Description')" placeholder="Rich tomato and cashew gravy with cottage cheese cubes..." rows="3" />
                        
                        <div class="pt-2">
                            <flux:button variant="primary" type="submit" class="w-full">{{ __('Add Menu Item') }}</flux:button>
                        </div>
                    </form>
                </div>

                <!-- Existing Menu Items Table (Right Column) -->
                <div class="lg:col-span-2 bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Menu Directory') }}</flux:heading>
                            <flux:subheading class="text-xs">{{ __('Manage categories, pricing rates, and stocks') }}</flux:subheading>
                        </div>
                        <!-- Search input -->
                        <div class="w-full sm:w-64">
                            <flux:input wire:model.live.debounce.150ms="searchMenu" icon="magnifying-glass" placeholder="Search menu dishes..." />
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[700px] text-left border-collapse">
                            <thead>
                                <tr class="border-b border-neutral-100 dark:border-stone-800 text-sm">
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Dish') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Category') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Price') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Availability') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-50 dark:divide-stone-800/40">
                                @forelse ($this->menuItemsList as $item)
                                    <tr class="hover:bg-neutral-50/50 dark:hover:bg-stone-800/20 transition-colors">
                                        <td class="py-4">
                                            <div class="font-bold text-neutral-800 dark:text-neutral-200">{{ $item->name }}</div>
                                            @if($item->description)
                                                <div class="text-[10px] text-neutral-500 max-w-xs truncate">{{ $item->description }}</div>
                                            @endif
                                        </td>
                                        <td class="py-4 text-center">
                                            <flux:badge size="xs" color="indigo" class="font-bold tracking-wide uppercase text-[8px]">{{ $item->category }}</flux:badge>
                                        </td>
                                        <td class="py-4 text-center font-bold text-neutral-800 dark:text-neutral-200">
                                            ₹{{ number_format($item->price, 2) }}
                                        </td>
                                        <td class="py-4 text-center">
                                            <div class="flex justify-center">
                                                <flux:badge color="{{ $item->is_available ? 'green' : 'red' }}" class="font-bold tracking-wide uppercase text-[8px] px-2 py-0.5">
                                                    {{ $item->is_available ? __('In Stock') : __('Out of Stock') }}
                                                </flux:badge>
                                            </div>
                                        </td>
                                        <td class="py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <flux:button size="xs" variant="ghost" class="text-xs font-semibold py-1 px-3 rounded-lg border border-stone-200 dark:border-stone-800" wire:click="toggleMenuItemAvailability({{ $item->id }})">
                                                    {{ $item->is_available ? __('Mark Out') : __('Mark In') }}
                                                </flux:button>
                                                <flux:button size="xs" variant="danger" class="font-bold py-1 px-3 rounded-lg border-none" wire:click="deleteMenuItem({{ $item->id }})">
                                                    {{ __('Delete') }}
                                                </flux:button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-12 text-center text-neutral-500">
                                            {{ __('No dishes found matching search query.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-4 border-t border-neutral-100 dark:border-stone-800">
                        {{ $this->menuItemsList->links() }}
                    </div>
                </div>
            </div>

        @elseif ($tab === 'blogs')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Publish Blog Form -->
                <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-4 self-start">
                    <div>
                        <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Publish Culinary Blog') }}</flux:heading>
                        <flux:subheading class="text-xs">{{ __('Publish culinary guides, receipts, and stories for diner profiles') }}</flux:subheading>
                    </div>

                    <form wire:submit="createBlog" class="space-y-4 pt-2">
                        <flux:input wire:model="blog_title" :label="__('Blog Title')" type="text" placeholder="The Art of Balancing Indian Spices" required />
                        <flux:textarea wire:model="blog_content" :label="__('Blog Content')" placeholder="Share your recipe tips, kitchen history, or special techniques with visitors..." rows="8" required />
                        <div class="pt-2">
                            <flux:button variant="primary" type="submit" class="w-full">{{ __('Publish Blog') }}</flux:button>
                        </div>
                    </form>
                </div>

                <!-- Published Blogs list -->
                <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-6">
                    <div>
                        <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Published Content') }}</flux:heading>
                        <flux:subheading class="text-xs">{{ __('Track and review published food stories') }}</flux:subheading>
                    </div>

                    <div class="space-y-4 divide-y divide-neutral-50 dark:divide-stone-800/40 max-h-[500px] overflow-y-auto pr-2">
                        @forelse ($this->blogsList as $blog)
                            <div class="pt-4 first:pt-0 pb-4 flex items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <h4 class="font-bold text-stone-850 dark:text-stone-100 text-sm leading-snug">{{ $blog->title }}</h4>
                                    <div class="text-[10px] text-neutral-400 font-mono">{{ $blog->created_at->format('M d, Y') }}</div>
                                    <p class="text-xs text-neutral-600 dark:text-neutral-400 line-clamp-3 leading-relaxed pt-1.5">{{ $blog->content }}</p>
                                </div>
                                <flux:button size="xs" variant="danger" class="font-bold py-1 px-3.5 rounded-lg border-none shrink-0" wire:click="deleteBlog({{ $blog->id }})">
                                    {{ __('Delete') }}
                                </flux:button>
                            </div>
                        @empty
                            <div class="text-center py-12 text-neutral-500 text-sm">{{ __('No blogs published yet.') }}</div>
                        @endforelse
                    </div>

                    <div class="pt-4 border-t border-neutral-100 dark:border-stone-800">
                        {{ $this->blogsList->links() }}
                    </div>
                </div>
            </div>

        @elseif ($tab === 'tables')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Add Dining Table form (Left Column) -->
                <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-4 self-start">
                    <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Add Seating') }}</flux:heading>
                    <flux:subheading class="text-xs">{{ __('Add physical dining tables to the floor layout') }}</flux:subheading>

                    <form wire:submit="createTable" class="space-y-4 pt-2">
                        <flux:input wire:model="table_name" :label="__('Table Name')" type="text" placeholder="Table 10" required />
                        <flux:input wire:model="table_capacity" :label="__('Seating Capacity')" type="number" min="1" max="20" required />
                        <div class="pt-2">
                            <flux:button variant="primary" type="submit" class="w-full">{{ __('Create Table') }}</flux:button>
                        </div>
                    </form>
                </div>

                <!-- Seating Cards Grid (Right Column) -->
                <div class="lg:col-span-2 bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-6">
                    <div>
                        <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Tables Layout Directory') }}</flux:heading>
                        <flux:subheading class="text-xs">{{ __('Audit details, capacities, and active/out-of-order settings') }}</flux:subheading>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[500px] overflow-y-auto pr-1">
                        @forelse ($this->tablesList as $t)
                            <div class="bg-neutral-50/40 dark:bg-stone-950/20 border border-neutral-200/80 dark:border-stone-850 rounded-2xl p-4 flex flex-col justify-between gap-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-black text-neutral-800 dark:text-neutral-100 text-sm">{{ $t->name }}</div>
                                        <div class="text-xs text-neutral-500 mt-1 flex items-center gap-1.5">
                                            <flux:icon name="users" class="size-3.5 text-neutral-400" />
                                            <span>{{ $t->capacity }} {{ __('seats capacity') }}</span>
                                        </div>
                                    </div>
                                    <flux:badge size="xs" color="{{ $t->status === 'available' ? 'green' : 'red' }}" class="font-bold tracking-wide uppercase text-[8px] px-2 py-0.5">
                                        {{ $t->status }}
                                    </flux:badge>
                                </div>
                                <div class="flex items-center justify-end">
                                    <flux:button size="xs" variant="ghost" class="text-xs font-semibold py-1 px-3 rounded-lg border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900" wire:click="toggleTableStatus({{ $t->id }})">
                                        {{ $t->status === 'available' ? __('Mark Out-of-Order') : __('Mark Active') }}
                                    </flux:button>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2 py-12 text-center text-neutral-500 text-sm">{{ __('No floor seating tables created.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>

        @elseif ($tab === 'staff')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Recruit Staff & Roster (Left Column) -->
                <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-6 self-start">
                    <div class="space-y-4">
                        <div>
                            <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Recruit Member') }}</flux:heading>
                            <flux:subheading class="text-xs">{{ __('Create shift credential logins for new restaurant staff') }}</flux:subheading>
                        </div>
                        <form wire:submit="addStaff" class="space-y-4 pt-2">
                            <flux:input wire:model="staff_name" :label="__('Full Name')" type="text" placeholder="Mario Rossi" required />
                            <flux:input wire:model="staff_email" :label="__('Email Address')" type="email" placeholder="staff@bellaitalia.com" required />
                            <div class="pt-2">
                                <flux:button variant="primary" type="submit" class="w-full">{{ __('Recruit Staff Account') }}</flux:button>
                            </div>
                        </form>
                    </div>

                    <!-- Staff List -->
                    <div class="border-t border-neutral-100 dark:border-stone-800 pt-6 space-y-4">
                        <flux:text size="sm" class="font-bold uppercase tracking-wider text-neutral-400 text-[10px]">{{ __('Current Roster') }}</flux:text>
                        <div class="divide-y divide-neutral-50 dark:divide-stone-800/40 max-h-56 overflow-y-auto pr-1">
                            @foreach ($this->staffList as $s)
                                <div class="flex items-center justify-between py-2 text-sm first:pt-0">
                                    <div>
                                        <div class="font-bold text-neutral-700 dark:text-stone-300">{{ $s->name }}</div>
                                        <div class="text-[10px] text-neutral-400 font-mono">{{ $s->email }}</div>
                                    </div>
                                    <flux:badge size="xs" color="indigo" class="font-black text-[8px] uppercase tracking-wide">Staff</flux:badge>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Shift Attendance Logs (Right Column) -->
                <div class="lg:col-span-2 bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-6">
                    <div>
                        <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Shift Attendance Logs') }}</flux:heading>
                        <flux:subheading class="text-xs">{{ __('Track check-in, check-out times, and durations') }}</flux:subheading>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[650px] text-left border-collapse">
                            <thead>
                                <tr class="border-b border-neutral-100 dark:border-stone-800 text-sm">
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Staff Member') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Check-In') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Check-Out') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-right">{{ __('Shift Duration') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-50 dark:divide-stone-800/40">
                                @forelse ($this->attendanceLogs as $log)
                                    <tr class="hover:bg-neutral-50/50 dark:hover:bg-stone-800/20 transition-colors">
                                        <td class="py-4 font-bold text-neutral-800 dark:text-neutral-200 text-sm">
                                            {{ $log->user->name }}
                                        </td>
                                        <td class="py-4 text-center text-xs text-neutral-700 dark:text-neutral-300">
                                            {{ $log->check_in->format('M d, Y') }}
                                            <div class="text-[10px] text-neutral-400 font-mono mt-0.5">{{ $log->check_in->format('h:i A') }}</div>
                                        </td>
                                        <td class="py-4 text-center text-xs text-neutral-700 dark:text-neutral-300">
                                            @if ($log->check_out)
                                                {{ $log->check_out->format('M d, Y') }}
                                                <div class="text-[10px] text-neutral-400 font-mono mt-0.5">{{ $log->check_out->format('h:i A') }}</div>
                                            @else
                                                <span class="text-neutral-400">-</span>
                                            @endif
                                        </td>
                                        <td class="py-4 text-right">
                                            @if ($log->check_out)
                                                <span class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">{{ $log->check_in->diffAsCarbonInterval($log->check_out)->forHumans(['short' => true]) }}</span>
                                            @else
                                                <span class="text-xs text-green-500 animate-pulse font-black uppercase tracking-wider">{{ __('On Duty') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-12 text-center text-neutral-500 text-sm">
                                            {{ __('No shift logs recorded.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-4 border-t border-neutral-100 dark:border-stone-800">
                        {{ $this->attendanceLogs->links() }}
                    </div>
                </div>
            </div>

        @elseif ($tab === 'profile')
            <div class="max-w-2xl bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-6">
                <div>
                    <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Restaurant Details') }}</flux:heading>
                    <flux:subheading class="text-xs">{{ __('Manage the public contact configurations, descriptions, and address of the bistro') }}</flux:subheading>
                </div>

                <form wire:submit="updateProfile" class="space-y-4 pt-2">
                    <!-- Current / Preview Cover Photo -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">{{ __('Restaurant Cover Image') }}</label>
                        <div class="relative w-full h-48 bg-stone-100 dark:bg-stone-950 border border-neutral-200 dark:border-stone-850 rounded-2xl overflow-hidden flex items-center justify-center">
                            @if ($resto_image)
                                <img src="{{ $resto_image->temporaryUrl() }}" class="w-full h-full object-cover" />
                            @elseif (Auth::user()->restaurant->image_path)
                                <img src="{{ asset('storage/' . Auth::user()->restaurant->image_path) }}" class="w-full h-full object-cover" />
                            @else
                                <div class="text-center text-neutral-400 dark:text-stone-500 space-y-2 p-6">
                                    <flux:icon name="photo" class="size-12 mx-auto" />
                                    <p class="text-xs">{{ __('No cover photo uploaded yet. Add a beautiful image to wow your diners.') }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Styled File Input -->
                        <div class="flex items-center justify-center w-full">
                            <label class="flex flex-col items-center justify-center w-full h-20 border border-neutral-300 border-dashed rounded-xl cursor-pointer bg-stone-50 hover:bg-neutral-50 dark:bg-stone-950 dark:hover:bg-stone-900 dark:border-stone-800 transition-colors">
                                <div class="flex flex-col items-center justify-center pt-2 pb-2">
                                    <flux:icon name="arrow-up-tray" class="size-5 text-neutral-400 mb-1" />
                                    <p class="text-xs text-neutral-500 dark:text-stone-400 font-semibold">{{ __('Click to upload new image (PNG, JPG, max 2MB)') }}</p>
                                </div>
                                <input type="file" wire:model="resto_image" class="hidden" accept="image/*" />
                            </label>
                        </div>
                        @error('resto_image')
                            <span class="text-xs text-red-500 font-bold block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <flux:input wire:model="resto_name" :label="__('Bistro Name')" type="text" required />
                    <flux:input wire:model="resto_email" :label="__('Contact Email')" type="email" required />
                    <flux:input wire:model="resto_phone" :label="__('Contact Phone')" type="text" required />
                    <flux:textarea wire:model="resto_address" :label="__('Physical Address')" rows="4" required />
                    
                    <div class="pt-4 flex justify-end">
                        <flux:button variant="primary" type="submit" class="px-8">{{ __('Save Restaurant Profile') }}</flux:button>
                    </div>
                </form>
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
                            <span class="text-xs text-stone-400 font-bold tracking-wide uppercase">{{ __('Bistro Live Logs') }}</span>
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
                        <span class="text-emerald-400 font-bold">manager@zayka-dining</span>:<span class="text-sky-400 font-bold">~/resto-app</span>$ tail -n 50 -f storage/logs/laravel.log | grep "Restaurant ID: {{ Auth::user()->restaurant_id }}"
                    </div>

                    @forelse ($this->restaurantLogs as $log)
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
                            <div class="text-stone-500 text-sm font-bold">{{ __('Console clear. No new bistro log entries.') }}</div>
                        </div>
                    @endforelse

                    @if (!empty($this->restaurantLogs))
                        <!-- Blinking Cursor Prompt Line -->
                        <div class="flex items-center gap-1.5 text-xs select-none pt-2 border-t border-stone-900/40">
                            <span class="text-stone-500">manager@zayka-dining:~/resto-app$</span>
                            <span class="inline-block w-2 h-4 bg-emerald-400 animate-pulse"></span>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
