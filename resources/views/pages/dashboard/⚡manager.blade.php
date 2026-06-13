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
use Livewire\Attributes\Computed;
use Flux\Flux;

new class extends Component {
    // Active Tab
    public string $activeTab = 'bookings';

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

    public function updateProfile(): void
    {
        $this->validate([
            'resto_name' => 'required|string|max:255',
            'resto_email' => 'required|email|max:255',
            'resto_phone' => 'required|string',
            'resto_address' => 'required|string',
        ]);

        $restaurant = Auth::user()->restaurant;
        $restaurant->update([
            'name' => $this->resto_name,
            'email' => $this->resto_email,
            'phone' => $this->resto_phone,
            'address' => $this->resto_address,
        ]);

        Flux::toast(variant: 'success', text: __('Restaurant profile updated.'));
    }

    public function createTable(): void
    {
        $this->validate([
            'table_name' => 'required|string|max:255',
            'table_capacity' => 'required|integer|min:1|max:20',
        ]);

        $restaurant = Auth::user()->restaurant;
        $restaurant->tables()->create([
            'name' => $this->table_name,
            'capacity' => $this->table_capacity,
            'status' => 'available',
        ]);

        Flux::toast(variant: 'success', text: __('Table created.'));
        $this->reset(['table_name', 'table_capacity']);
    }

    public function toggleTableStatus(int $id): void
    {
        $table = Table::findOrFail($id);
        $table->update([
            'status' => $table->status === 'available' ? 'out_of_order' : 'available'
        ]);

        Flux::toast(variant: 'success', text: __('Table status toggled.'));
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

        Flux::toast(variant: 'success', text: __('Staff member registered. They can log in via OTP.'));
        $this->reset(['staff_name', 'staff_email']);
    }

    public function updateBookingStatus(int $id, string $status): void
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => $status]);

        // Send Notification
        try {
            $booking->user->notify(new \App\Notifications\BookingStatusNotification($booking, $status));
        } catch (\Exception $e) {
            logger()->error('Notification failed: ' . $e->getMessage());
        }

        Flux::toast(variant: 'success', text: __('Booking status updated.'));
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
        $restaurant->menuItems()->create([
            'name' => $this->menu_name,
            'description' => $this->menu_description,
            'price' => (float)$this->menu_price,
            'category' => $this->menu_category,
            'is_available' => true,
        ]);

        Flux::toast(variant: 'success', text: __('Menu item added.'));
        $this->reset(['menu_name', 'menu_description', 'menu_price', 'menu_category']);
    }

    public function toggleMenuItemAvailability(int $id): void
    {
        $item = MenuItem::findOrFail($id);
        $item->update([
            'is_available' => !$item->is_available
        ]);

        Flux::toast(variant: 'success', text: __('Menu item availability toggled.'));
    }

    public function deleteMenuItem(int $id): void
    {
        $item = MenuItem::findOrFail($id);
        $item->delete();

        Flux::toast(variant: 'success', text: __('Menu item deleted.'));
    }

    public function createBlog(): void
    {
        $this->validate([
            'blog_title' => 'required|string|max:255',
            'blog_content' => 'required|string',
        ]);

        $restaurant = Auth::user()->restaurant;
        $restaurant->blogs()->create([
            'title' => $this->blog_title,
            'slug' => Str::slug($this->blog_title),
            'content' => $this->blog_content,
        ]);

        Flux::toast(variant: 'success', text: __('Blog post published.'));
        $this->reset(['blog_title', 'blog_content']);
    }

    public function deleteBlog(int $id): void
    {
        $blog = Blog::findOrFail($id);
        $blog->delete();

        Flux::toast(variant: 'success', text: __('Blog post deleted.'));
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
    public function bookings(): \Illuminate\Database\Eloquent\Collection
    {
        $restaurantId = Auth::user()->restaurant_id;
        return Booking::where('restaurant_id', $restaurantId)->with(['user', 'table'])->latest()->get();
    }

    #[Computed]
    public function tablesList(): \Illuminate\Database\Eloquent\Collection
    {
        $restaurantId = Auth::user()->restaurant_id;
        return Table::where('restaurant_id', $restaurantId)->get();
    }

    #[Computed]
    public function staffList(): \Illuminate\Database\Eloquent\Collection
    {
        $restaurantId = Auth::user()->restaurant_id;
        return User::where('restaurant_id', $restaurantId)->role('staff')->get();
    }

    #[Computed]
    public function attendanceLogs(): \Illuminate\Database\Eloquent\Collection
    {
        $restaurantId = Auth::user()->restaurant_id;
        return Attendance::where('restaurant_id', $restaurantId)->with('user')->latest()->take(15)->get();
    }

    #[Computed]
    public function menuItemsList(): \Illuminate\Database\Eloquent\Collection
    {
        $restaurantId = Auth::user()->restaurant_id;
        return MenuItem::where('restaurant_id', $restaurantId)->get();
    }

    #[Computed]
    public function blogsList(): \Illuminate\Database\Eloquent\Collection
    {
        $restaurantId = Auth::user()->restaurant_id;
        return Blog::where('restaurant_id', $restaurantId)->latest()->get();
    }
}; ?>

    <div class="space-y-8 p-6 max-w-7xl mx-auto"
         x-data="{
             init() {
                 const checkHash = () => {
                     const hash = window.location.hash;
                     if (!hash) return;
                     
                     if (hash === '#reservations-console' || hash === '#attendance-logs') {
                         $wire.activeTab = 'bookings';
                     } else if (hash === '#menu-management') {
                         $wire.activeTab = 'menu';
                     } else if (hash === '#blog-management') {
                         $wire.activeTab = 'blogs';
                     }
                     
                     setTimeout(() => {
                         const el = document.querySelector(hash);
                         if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                     }, 100);
                 };
                 window.addEventListener('hashchange', checkHash);
                 checkHash();
             }
         }">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('Restaurant Console') }}</flux:heading>
                <flux:subheading>{{ __('Manage ' . Auth::user()->restaurant->name . ' profile, seating tables, staff roster, and reservation approvals') }}</flux:subheading>
            </div>
            <flux:badge color="indigo" class="text-sm font-semibold uppercase">{{ __('Manager') }}</flux:badge>
        </div>

        <!-- KPI metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs flex items-center justify-between">
                <div>
                    <flux:text size="sm" class="text-neutral-500">{{ __('Monthly Revenue') }}</flux:text>
                    <flux:heading size="xl" class="mt-1 font-bold text-green-600 dark:text-green-400">₹{{ number_format($this->stats['monthly_revenue'], 2) }}</flux:heading>
                </div>
                <flux:icon name="currency-dollar" class="size-8 text-green-500" />
            </div>

            <div class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs flex items-center justify-between">
                <div>
                    <flux:text size="sm" class="text-neutral-500">{{ __('Dining Tables') }}</flux:text>
                    <flux:heading size="xl" class="mt-1 font-bold">{{ $this->stats['total_tables'] }}</flux:heading>
                </div>
                <flux:icon name="table-cells" class="size-8 text-indigo-500" />
            </div>

            <div class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs flex items-center justify-between">
                <div>
                    <flux:text size="sm" class="text-neutral-500">{{ __('Staff Roster') }}</flux:text>
                    <flux:heading size="xl" class="mt-1 font-bold">{{ $this->stats['total_staff'] }}</flux:heading>
                </div>
                <flux:icon name="users" class="size-8 text-indigo-500" />
            </div>

            <div class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs flex items-center justify-between">
                <div>
                    <flux:text size="sm" class="text-neutral-500">{{ __('Pending Approvals') }}</flux:text>
                    <flux:heading size="xl" class="mt-1 font-bold text-amber-500">{{ $this->stats['pending_bookings'] }}</flux:heading>
                </div>
                <flux:icon name="clock" class="size-8 text-amber-500" />
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Side: Bookings & Roster, Menu, or Blogs Tabbed Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Tab bar -->
                <div class="flex border-b border-neutral-200 dark:border-stone-800 gap-6">
                    <button wire:click="$set('activeTab', 'bookings')" class="pb-3 text-sm font-semibold border-b-2 transition-all flex items-center gap-2 {{ $activeTab === 'bookings' ? 'border-amber-600 text-amber-600 font-bold' : 'border-transparent text-neutral-500 hover:text-neutral-700 dark:hover:text-stone-300' }}">
                        <flux:icon name="calendar-days" class="size-4" />
                        <span>{{ __('Reservations & Staff') }}</span>
                    </button>
                    <button wire:click="$set('activeTab', 'menu')" class="pb-3 text-sm font-semibold border-b-2 transition-all flex items-center gap-2 {{ $activeTab === 'menu' ? 'border-amber-600 text-amber-600 font-bold' : 'border-transparent text-neutral-500 hover:text-neutral-700 dark:hover:text-stone-300' }}">
                        <flux:icon name="list-bullet" class="size-4" />
                        <span>{{ __('Royal Menu') }}</span>
                    </button>
                    <button wire:click="$set('activeTab', 'blogs')" class="pb-3 text-sm font-semibold border-b-2 transition-all flex items-center gap-2 {{ $activeTab === 'blogs' ? 'border-amber-600 text-amber-600 font-bold' : 'border-transparent text-neutral-500 hover:text-neutral-700 dark:hover:text-stone-300' }}">
                        <flux:icon name="pencil-square" class="size-4" />
                        <span>{{ __('Culinary Blogs') }}</span>
                    </button>
                </div>

                @if ($activeTab === 'bookings')
                    <!-- Bookings approval console -->
                    <div id="reservations-console" class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs">
                        <flux:heading size="lg" class="mb-4">{{ __('Reservations Console') }}</flux:heading>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-neutral-100 dark:border-neutral-800">
                                        <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Customer') }}</th>
                                        <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Details') }}</th>
                                        <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Payments') }}</th>
                                        <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Status') }}</th>
                                        <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-right">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($this->bookings as $booking)
                                        <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-neutral-50/50">
                                            <td class="py-3 font-medium">
                                                <div>{{ $booking->user->name }}</div>
                                                <div class="text-xs text-neutral-500">{{ $booking->user->email }}</div>
                                            </td>
                                            <td class="py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                                <div>{{ $booking->booking_date->format('M d, Y') }} @ {{ \Carbon\Carbon::parse($booking->booking_time)->format('h:i A') }}</div>
                                                <div class="text-xs">{{ $booking->guest_count }} Guests | Table: {{ $booking->table ? $booking->table->name : 'N/A' }}</div>
                                            </td>
                                            <td class="py-3 text-sm">
                                                <div>₹{{ number_format($booking->payment_amount, 2) }}</div>
                                                <flux:badge color="{{ str_contains($booking->payment_status, 'paid') ? 'green' : 'amber' }}" size="xs">
                                                    {{ $booking->payment_status }}
                                                </flux:badge>
                                            </td>
                                            <td class="py-3">
                                                <flux:badge color="{{ $booking->status === 'confirmed' ? 'green' : ($booking->status === 'pending' ? 'amber' : 'red') }}">
                                                    {{ $booking->status }}
                                                </flux:badge>
                                            </td>
                                            <td class="py-3 text-right">
                                                <div class="flex items-center justify-end gap-1">
                                                    @if ($booking->status === 'pending')
                                                        <flux:button size="xs" variant="primary" class="bg-green-600 hover:bg-green-700 text-white" wire:click="updateBookingStatus({{ $booking->id }}, 'confirmed')">{{ __('Approve') }}</flux:button>
                                                        <flux:button size="xs" variant="danger" wire:click="updateBookingStatus({{ $booking->id }}, 'rejected')">{{ __('Reject') }}</flux:button>
                                                    @elseif ($booking->status === 'confirmed')
                                                        <flux:button size="xs" variant="ghost" wire:click="updateBookingStatus({{ $booking->id }}, 'completed')">{{ __('Mark Completed') }}</flux:button>
                                                        <flux:button size="xs" variant="danger" wire:click="updateBookingStatus({{ $booking->id }}, 'cancelled')">{{ __('Cancel') }}</flux:button>
                                                    @endif
                                                    @if (str_contains($booking->payment_status, 'paid'))
                                                        <flux:button size="xs" variant="ghost" icon="document-text" as="a" href="{{ route('booking.invoice', ['id' => $booking->id]) }}" target="_blank" />
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-6 text-center text-neutral-400">{{ __('No bookings found.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Staff Attendance Logs -->
                    <div id="attendance-logs" class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs">
                        <flux:heading size="lg" class="mb-4">{{ __('Staff Attendance Logs') }}</flux:heading>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-neutral-100 dark:border-neutral-800">
                                        <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Staff Member') }}</th>
                                        <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Check-In') }}</th>
                                        <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Check-Out') }}</th>
                                        <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Duration') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($this->attendanceLogs as $log)
                                        <tr class="border-b border-neutral-100 dark:border-neutral-800 text-sm">
                                            <td class="py-3 font-medium">{{ $log->user->name }}</td>
                                            <td class="py-3">{{ $log->check_in->format('M d, H:i A') }}</td>
                                            <td class="py-3">{{ $log->check_out ? $log->check_out->format('M d, H:i A') : 'On Shift' }}</td>
                                            <td class="py-3 font-semibold">
                                                @if ($log->check_out)
                                                    {{ $log->check_in->diffAsCarbonInterval($log->check_out)->forHumans(['short' => true]) }}
                                                @else
                                                    <span class="text-green-500 animate-pulse font-bold">{{ __('Active') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-6 text-center text-neutral-400">{{ __('No shifts recorded.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif ($activeTab === 'menu')
                    <!-- Manage Menu -->
                    <div id="menu-management" class="space-y-6">
                        <!-- Add Menu Item form -->
                        <div class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs">
                            <flux:heading size="lg" class="mb-4">{{ __('Add Royal Menu Item') }}</flux:heading>
                            <form wire:submit="createMenuItem" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <flux:input wire:model="menu_name" :label="__('Item Name')" type="text" placeholder="Paneer Butter Masala" required />
                                    <flux:input wire:model="menu_price" :label="__('Price (₹)')" type="number" step="0.01" min="0" placeholder="320" required />
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <flux:select wire:model="menu_category" :label="__('Category')">
                                        <option value="Starters">{{ __('Starters') }}</option>
                                        <option value="Main Course">{{ __('Main Course') }}</option>
                                        <option value="Desserts">{{ __('Desserts') }}</option>
                                        <option value="Beverages">{{ __('Beverages') }}</option>
                                    </flux:select>
                                    <flux:input wire:model="menu_description" :label="__('Description')" type="text" placeholder="Rich tomato and cashew gravy with cottage cheese cubes" />
                                </div>
                                <div class="flex justify-end pt-2">
                                    <flux:button variant="primary" type="submit">{{ __('Add Menu Item') }}</flux:button>
                                </div>
                            </form>
                        </div>

                        <!-- Menu Items list -->
                        <div class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs">
                            <flux:heading size="lg" class="mb-4">{{ __('Existing Menu Items') }}</flux:heading>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-neutral-100 dark:border-neutral-800">
                                            <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Item Name') }}</th>
                                            <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Category') }}</th>
                                            <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Price') }}</th>
                                            <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Availability') }}</th>
                                            <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-right">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($this->menuItemsList as $item)
                                            <tr class="border-b border-neutral-100 dark:border-neutral-800 text-sm hover:bg-neutral-50/50">
                                                <td class="py-3 font-medium">
                                                    <div>{{ $item->name }}</div>
                                                    @if($item->description)
                                                        <div class="text-xs text-neutral-500 line-clamp-1">{{ $item->description }}</div>
                                                    @endif
                                                </td>
                                                <td class="py-3">
                                                    <flux:badge size="xs" color="indigo">{{ $item->category }}</flux:badge>
                                                </td>
                                                <td class="py-3 font-semibold">
                                                    ₹{{ number_format($item->price, 2) }}
                                                </td>
                                                <td class="py-3">
                                                    <flux:badge color="{{ $item->is_available ? 'green' : 'red' }}">
                                                        {{ $item->is_available ? __('In Stock') : __('Out of Stock') }}
                                                    </flux:badge>
                                                </td>
                                                <td class="py-3 text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <flux:button size="xs" variant="ghost" wire:click="toggleMenuItemAvailability({{ $item->id }})">
                                                            {{ $item->is_available ? __('Mark Out of Stock') : __('Mark In Stock') }}
                                                        </flux:button>
                                                        <flux:button size="xs" variant="danger" wire:click="deleteMenuItem({{ $item->id }})">
                                                            {{ __('Delete') }}
                                                        </flux:button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="py-6 text-center text-neutral-400">{{ __('No menu items found.') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @elseif ($activeTab === 'blogs')
                    <!-- Culinary Blogs -->
                    <div id="blog-management" class="space-y-6">
                        <!-- Write a New Blog Post form -->
                        <div class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs">
                            <flux:heading size="lg" class="mb-4">{{ __('Publish a Culinary Blog') }}</flux:heading>
                            <form wire:submit="createBlog" class="space-y-4">
                                <flux:input wire:model="blog_title" :label="__('Blog Title')" type="text" placeholder="The Art of Balancing Indian Spices" required />
                                <flux:textarea wire:model="blog_content" :label="__('Blog Content')" placeholder="Share your recipe tips, kitchen history, or special techniques with visitors..." rows="6" required />
                                <div class="flex justify-end pt-2">
                                    <flux:button variant="primary" type="submit">{{ __('Publish Blog') }}</flux:button>
                                </div>
                            </form>
                        </div>

                        <!-- Published Blogs list -->
                        <div class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs">
                            <flux:heading size="lg" class="mb-4">{{ __('Published Blogs') }}</flux:heading>
                            <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                                @forelse ($this->blogsList as $blog)
                                    <div class="border-b border-neutral-100 dark:border-neutral-800 pb-4 last:border-0 last:pb-0 flex items-start justify-between gap-4">
                                        <div class="space-y-1">
                                            <h4 class="font-bold text-stone-800 dark:text-stone-100">{{ $blog->title }}</h4>
                                            <span class="text-xs text-neutral-500 font-mono">{{ $blog->created_at->format('M d, Y') }}</span>
                                            <p class="text-xs text-neutral-600 dark:text-neutral-400 line-clamp-2 leading-relaxed">{{ $blog->content }}</p>
                                        </div>
                                        <flux:button size="xs" variant="danger" wire:click="deleteBlog({{ $blog->id }})">
                                            {{ __('Delete') }}
                                        </flux:button>
                                    </div>
                                @empty
                                    <div class="text-center py-6 text-neutral-400">{{ __('No blogs published yet.') }}</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Side: Configuration Panels -->
            <div class="space-y-6">
                <!-- Manage Profile -->
                <div id="restaurant-profile" class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs">
                    <flux:heading size="lg" class="mb-4">{{ __('Restaurant Details') }}</flux:heading>
                    <form wire:submit="updateProfile" class="space-y-4">
                        <flux:input wire:model="resto_name" :label="__('Name')" type="text" required />
                        <flux:input wire:model="resto_email" :label="__('Email')" type="email" required />
                        <flux:input wire:model="resto_phone" :label="__('Phone')" type="text" required />
                        <flux:textarea wire:model="resto_address" :label="__('Address')" required />
                        <flux:button variant="primary" type="submit" class="w-full">Save Profile</flux:button>
                    </form>
                </div>

                <!-- Table Setup -->
                <div id="seating-tables" class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs">
                    <flux:heading size="lg" class="mb-4">{{ __('Manage Dining Tables') }}</flux:heading>
                    
                    <!-- Table add form -->
                    <form wire:submit="createTable" class="flex items-end gap-2 mb-6 border-b border-neutral-100 dark:border-neutral-800 pb-4">
                        <div class="flex-1">
                            <flux:input wire:model="table_name" :label="__('Table Name')" type="text" placeholder="Table 10" required />
                        </div>
                        <div class="w-20">
                            <flux:input wire:model="table_capacity" :label="__('Capacity')" type="number" min="1" max="20" required />
                        </div>
                        <flux:button variant="primary" type="submit">Add</flux:button>
                    </form>

                    <!-- Table list -->
                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        @foreach ($this->tablesList as $t)
                            <div class="flex items-center justify-between text-sm py-2 border-b border-neutral-50 dark:border-neutral-800/40">
                                <div>
                                    <span class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $t->name }}</span>
                                    <span class="text-xs text-neutral-500">({{ $t->capacity }} seats)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <flux:badge color="{{ $t->status === 'available' ? 'green' : 'red' }}">
                                        {{ $t->status }}
                                    </flux:badge>
                                    <flux:button size="xs" variant="ghost" wire:click="toggleTableStatus({{ $t->id }})">Toggle</flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Staff Recruitment -->
                <div id="staff-roster" class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs">
                    <flux:heading size="lg" class="mb-4">{{ __('Recruit Staff Member') }}</flux:heading>
                    <form wire:submit="addStaff" class="space-y-4 mb-6">
                        <flux:input wire:model="staff_name" :label="__('Full Name')" type="text" placeholder="Mario Rossi" required />
                        <flux:input wire:model="staff_email" :label="__('Email Address')" type="email" placeholder="staff@bellaitalia.com" required />
                        <flux:button variant="primary" type="submit" class="w-full">Create Staff Account</flux:button>
                    </form>

                    <!-- Staff List -->
                    <div class="space-y-2 border-t border-neutral-100 dark:border-neutral-800 pt-4">
                        <flux:text size="sm" class="font-semibold text-neutral-500">{{ __('Current Staff Roster') }}</flux:text>
                        @foreach ($this->staffList as $s)
                            <div class="flex items-center justify-between text-sm py-1">
                                <div>
                                    <div class="font-medium">{{ $s->name }}</div>
                                    <div class="text-xs text-neutral-500">{{ $s->email }}</div>
                                </div>
                                <flux:badge size="xs" color="indigo">Staff</flux:badge>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
