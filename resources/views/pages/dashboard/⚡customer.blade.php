<?php

use Livewire\Component;
use App\Models\Restaurant;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Flux\Flux;

new class extends Component {
    use WithPagination;

    #[Url(keep: true)]
    public string $tab = 'dashboard';

    // Booking Form fields
    public int $selectedRestaurantId = 0;
    public int $guestCount = 2;
    public string $bookingDate = '';
    public string $bookingTime = '19:00';
    public string $paymentType = 'advance'; // advance, full
    public string $notes = '';
    public string $searchTerm = '';
    public string $historyFilter = 'all'; // all, pending, confirmed, completed_cancelled
    public string $activeCuisineFilter = 'all'; // all, mughlai, rajasthani, top_rated

    public function mount(): void
    {
        $this->bookingDate = now()->addDay()->toDateString();
        $firstResto = Restaurant::where('is_active', true)->first();
        if ($firstResto) {
            $this->selectedRestaurantId = $firstResto->id;
        }
    }

    public function selectRestaurant(int $id): void
    {
        $this->selectedRestaurantId = $id;
        Flux::toast(variant: 'success', text: __('Restaurant selected. Please fill booking details.'));
    }

    public function selectRestaurantAndSlot(int $id, string $time): void
    {
        $this->selectedRestaurantId = $id;
        $this->bookingTime = $time;
        Flux::toast(variant: 'success', text: __('Restaurant and time slot selected!'));
    }

    public function makeBooking(): void
    {
        $this->validate([
            'selectedRestaurantId' => 'required|exists:restaurants,id',
            'guestCount' => 'required|integer|min:1|max:20',
            'bookingDate' => 'required|date|after_or_equal:today',
            'bookingTime' => 'required|string',
            'paymentType' => 'required|in:advance,full',
            'notes' => 'nullable|string',
        ]);

        $bookingService = new BookingService();

        try {
            // Check availability first
            $availableTable = $bookingService->findAvailableTable(
                $this->selectedRestaurantId,
                $this->bookingDate,
                $this->bookingTime,
                $this->guestCount
            );

            if (! $availableTable) {
                Flux::toast(variant: 'danger', text: __('No tables are available for the selected slot and guest count. Try another slot.'));
                return;
            }

            // Create booking
            $booking = $bookingService->createBooking(
                $this->selectedRestaurantId,
                Auth::id(),
                $this->guestCount,
                $this->bookingDate,
                $this->bookingTime,
                $this->paymentType,
                $this->notes ?? ''
            );

            Flux::toast(variant: 'success', text: __('Booking created. Redirecting to Stripe Payment...'));

            // Redirect to Stripe checkout
            $this->redirect(route('booking.pay', ['id' => $booking->id]));
        } catch (\Exception $e) {
            logger()->error('Booking creation failed: ' . $e->getMessage());
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
    }

    public function cancelBooking(int $id): void
    {
        $booking = Booking::where('user_id', Auth::id())->findOrFail($id);

        if (in_array($booking->status, ['completed', 'cancelled', 'rejected'])) {
            Flux::toast(variant: 'danger', text: __('This booking cannot be cancelled.'));
            return;
        }

        $booking->update([
            'status' => 'cancelled',
        ]);

        Flux::toast(variant: 'success', text: __('Your reservation has been cancelled successfully.'));
    }

    public function getCuisineTag(string $name): string
    {
        $lower = strtolower($name);
        if (str_contains($lower, 'spice') || str_contains($lower, 'symphony')) {
            return 'Mughlai Fusion';
        }
        if (str_contains($lower, 'maharaja')) {
            return 'Rajasthani Royal';
        }
        if (str_contains($lower, 'haveli') || str_contains($lower, 'zayka')) {
            return 'Awadhi Mughlai';
        }
        return 'North Indian Fine Dining';
    }

    public function updatingHistoryFilter(): void
    {
        $this->resetPage('bookingsPage');
    }

    public function updatingSearchTerm(): void
    {
        $this->resetPage('bookingsPage');
    }

    #[Computed]
    public function stats(): array
    {
        $userId = Auth::id();
        return [
            'total' => Booking::where('user_id', $userId)->count(),
            'confirmed' => Booking::where('user_id', $userId)->where('status', 'confirmed')->count(),
            'pending' => Booking::where('user_id', $userId)->where('status', 'pending')->count(),
            'spent' => Booking::where('user_id', $userId)
                ->whereIn('payment_status', ['paid_full', 'paid_advance'])
                ->sum('payment_amount'),
        ];
    }

    #[Computed]
    public function restaurants(): \Illuminate\Database\Eloquent\Collection
    {
        return Restaurant::where('is_active', true)
            ->when($this->searchTerm, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('address', 'like', '%' . $this->searchTerm . '%');
                });
            })
            ->when($this->activeCuisineFilter !== 'all', function ($query) {
                if ($this->activeCuisineFilter === 'top_rated') {
                    $query->where('rating', '>=', 4.8);
                } elseif ($this->activeCuisineFilter === 'mughlai') {
                    $query->where(function ($q) {
                        $q->where('name', 'like', '%spice%')
                          ->orWhere('name', 'like', '%haveli%')
                          ->orWhere('name', 'like', '%zayka%');
                    });
                } elseif ($this->activeCuisineFilter === 'rajasthani') {
                    $query->where('name', 'like', '%maharaja%');
                }
            })
            ->get();
    }

    #[Computed]
    public function myBookings(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Booking::where('user_id', Auth::id())
            ->when($this->historyFilter === 'pending', function ($query) {
                $query->where('status', 'pending');
            })
            ->when($this->historyFilter === 'confirmed', function ($query) {
                $query->where('status', 'confirmed');
            })
            ->when($this->historyFilter === 'completed_cancelled', function ($query) {
                $query->whereIn('status', ['completed', 'cancelled', 'rejected']);
            })
            ->with(['restaurant', 'table'])
            ->latest()
            ->paginate(5, ['*'], 'bookingsPage');
    }

    #[Computed]
    public function recentBookings(): \Illuminate\Database\Eloquent\Collection
    {
        return Booking::where('user_id', Auth::id())
            ->with(['restaurant', 'table'])
            ->latest()
            ->take(3)
            ->get();
    }
}; ?>

    <div class="space-y-8 p-6 max-w-7xl mx-auto transition-all duration-300">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-amber-900/10 dark:border-amber-100/10 pb-6">
            <div>
                @if ($tab === 'dashboard')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-amber-600 to-orange-600 dark:from-amber-400 dark:to-orange-400 bg-clip-text text-transparent">{{ __('Namaste, :name!', ['name' => auth()->user()->name]) }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Manage your profile, check upcoming tables, or reserve new dining spots.') }}</flux:subheading>
                @elseif ($tab === 'reserve')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-amber-600 to-orange-600 dark:from-amber-400 dark:to-orange-400 bg-clip-text text-transparent">{{ __('Reserve a Royal Table') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Select one of our fine-dining restaurants and complete your booking.') }}</flux:subheading>
                @elseif ($tab === 'history')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-amber-600 to-orange-600 dark:from-amber-400 dark:to-orange-400 bg-clip-text text-transparent">{{ __('Reservation History') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Track, review, or cancel your dining history records.') }}</flux:subheading>
                @endif
            </div>
            <div>
                <flux:badge color="amber" class="text-sm font-bold px-3 py-1 shadow-sm uppercase tracking-wider bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50">{{ __('Diner Account') }}</flux:badge>
            </div>
        </div>

        @if (session('status') === 'payment-successful')
            <div class="bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-800/40 rounded-xl p-4 text-green-800 dark:text-green-400 text-sm flex items-center gap-2">
                <flux:icon name="check-circle" class="size-5 text-green-500" />
                <span>{{ __('Payment processed successfully! Your reservation has been submitted for restaurant approval.') }}</span>
            </div>
        @endif

        @if ($tab === 'dashboard')
            <!-- KPI Metrics Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Bookings -->
                <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs flex items-center justify-between hover:shadow-md transition-all duration-300 cursor-pointer" wire:click="$set('tab', 'history')">
                    <div>
                        <flux:text size="sm" class="text-neutral-500 dark:text-neutral-400 font-medium tracking-wide uppercase">{{ __('Total Bookings') }}</flux:text>
                        <flux:heading size="xl" class="mt-2 font-black text-neutral-800 dark:text-neutral-100">{{ $this->stats['total'] }}</flux:heading>
                    </div>
                    <div class="p-3 bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-xl">
                        <flux:icon name="calendar" class="size-6" />
                    </div>
                </div>

                <!-- Confirmed Tables -->
                <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs flex items-center justify-between hover:shadow-md transition-all duration-300">
                    <div>
                        <flux:text size="sm" class="text-neutral-500 dark:text-neutral-400 font-medium tracking-wide uppercase">{{ __('Confirmed Tables') }}</flux:text>
                        <flux:heading size="xl" class="mt-2 font-black text-green-600 dark:text-green-400">{{ $this->stats['confirmed'] }}</flux:heading>
                    </div>
                    <div class="p-3 bg-green-500/10 dark:bg-green-500/20 text-green-600 dark:text-green-400 rounded-xl">
                        <flux:icon name="check-circle" class="size-6" />
                    </div>
                </div>

                <!-- Pending Approvals -->
                <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs flex items-center justify-between hover:shadow-md transition-all duration-300">
                    <div>
                        <flux:text size="sm" class="text-neutral-500 dark:text-neutral-400 font-medium tracking-wide uppercase">{{ __('Pending Approvals') }}</flux:text>
                        <flux:heading size="xl" class="mt-2 font-black text-amber-500">{{ $this->stats['pending'] }}</flux:heading>
                    </div>
                    <div class="p-3 bg-amber-500/5 dark:bg-amber-500/10 text-amber-500 rounded-xl">
                        <flux:icon name="clock" class="size-6" />
                    </div>
                </div>

                <!-- Total Spent -->
                <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs flex items-center justify-between hover:shadow-md transition-all duration-300">
                    <div>
                        <flux:text size="sm" class="text-neutral-500 dark:text-neutral-400 font-medium tracking-wide uppercase">{{ __('Total Spent') }}</flux:text>
                        <flux:heading size="xl" class="mt-2 font-black text-emerald-600 dark:text-emerald-400">₹{{ number_format($this->stats['spent'], 2) }}</flux:heading>
                    </div>
                    <div class="p-3 bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-xl">
                        <flux:icon name="credit-card" class="size-6" />
                    </div>
                </div>
            </div>

            <!-- Dashboard Columns -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Recent Reservations Snapshot (Left Column 2-span) -->
                <div class="lg:col-span-2 bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-neutral-100 dark:border-stone-800 pb-3">
                        <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Recent Reservation Activity') }}</flux:heading>
                        <flux:button variant="ghost" size="xs" wire:click="$set('tab', 'history')" class="text-xs font-semibold text-amber-600 dark:text-amber-400">
                            {{ __('View All') }} &rarr;
                        </flux:button>
                    </div>

                    <div class="divide-y divide-neutral-150 dark:divide-stone-850">
                        @forelse ($this->recentBookings as $b)
                            <div class="flex items-center justify-between py-4">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-lg">
                                        <flux:icon name="calendar" class="size-5" />
                                    </div>
                                    <div>
                                        <div class="font-bold text-neutral-800 dark:text-neutral-200">{{ $b->restaurant->name }}</div>
                                        <div class="text-xs text-neutral-500">
                                            {{ $b->guest_count }} {{ __('Guests') }} &bull; {{ $b->table ? $b->table->name : __('Assigned on arrival') }}
                                        </div>
                                        @if ($b->notes)
                                            <div class="mt-1 flex items-start gap-1 text-xs text-amber-600 dark:text-amber-400 bg-amber-500/5 dark:bg-amber-500/10 px-2 py-0.5 rounded-md border border-amber-500/10 max-w-md">
                                                <flux:icon name="chat-bubble-bottom-center-text" class="size-3 shrink-0 mt-0.5" />
                                                <span class="truncate">{{ $b->notes }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                                        {{ $b->booking_date->format('M d, Y') }}
                                    </div>
                                    <div class="text-xs text-neutral-500 font-mono">
                                        {{ \Carbon\Carbon::parse($b->booking_time)->format('h:i A') }}
                                    </div>
                                    <flux:badge size="xs" color="{{ $b->status === 'confirmed' ? 'green' : ($b->status === 'pending' ? 'amber' : 'red') }}" class="font-semibold uppercase tracking-wider text-[8px] mt-1">
                                        {{ $b->status }}
                                    </flux:badge>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-neutral-500 text-sm">
                                {{ __('No reservations scheduled yet.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Quick Action Card (Right Column) -->
                <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs flex flex-col justify-between gap-6 self-start relative overflow-hidden group">
                    <div class="absolute right-0 top-0 size-24 bg-amber-500/5 dark:bg-amber-500/10 rounded-bl-full pointer-events-none"></div>
                    <div class="space-y-3">
                        <div class="p-3 bg-amber-500/10 text-amber-600 rounded-xl w-fit">
                            <flux:icon name="plus-circle" class="size-6" />
                        </div>
                        <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Reserve a Royal Table') }}</flux:heading>
                        <flux:text class="text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed">
                            {{ __('Book a table instantly, select custom guest seats, write requests/notes, and checkout securely with Stripe.') }}
                        </flux:text>
                    </div>

                    <flux:button variant="primary" class="w-full font-bold py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl border-none cursor-pointer" wire:click="$set('tab', 'reserve')">
                        {{ __('Reserve a Table Now') }}
                    </flux:button>
                </div>
            </div>

        @elseif ($tab === 'reserve')
            <!-- Top Section: Restaurant List & Booking Form -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left: Restaurant List with Search (7 cols) -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <flux:heading size="lg">{{ __('Select a Restaurant') }}</flux:heading>
                                <flux:subheading>{{ __('Pick a place and select a time slot.') }}</flux:subheading>
                            </div>
                            <span class="text-xs text-stone-550 dark:text-stone-400 font-bold bg-stone-100 dark:bg-stone-850 px-3 py-1.5 rounded-full w-fit">{{ $this->restaurants->count() }} {{ __('available') }}</span>
                        </div>

                        <!-- Search Input -->
                        <flux:input 
                            wire:model.live="searchTerm" 
                            icon="magnifying-glass" 
                            placeholder="{{ __('Search by name or address...') }}" 
                        />

                        <!-- Cuisine Filter Tag Bar -->
                        <div class="flex gap-2 pb-2 overflow-x-auto whitespace-nowrap scrollbar-none">
                            <button wire:click="$set('activeCuisineFilter', 'all')" class="px-4 py-2 text-xs font-semibold rounded-full transition-all border {{ $activeCuisineFilter === 'all' ? 'bg-amber-600 border-amber-600 text-white' : 'bg-white hover:bg-stone-50 text-stone-600 dark:bg-stone-900 border-neutral-200 dark:border-stone-800 dark:text-stone-300' }}">
                                {{ __('All Cuisines') }}
                            </button>
                            <button wire:click="$set('activeCuisineFilter', 'mughlai')" class="px-4 py-2 text-xs font-semibold rounded-full transition-all border {{ $activeCuisineFilter === 'mughlai' ? 'bg-amber-600 border-amber-600 text-white' : 'bg-white hover:bg-stone-50 text-stone-600 dark:bg-stone-900 border-neutral-200 dark:border-stone-800 dark:text-stone-300' }}">
                                {{ __('Mughlai & Awadhi') }}
                            </button>
                            <button wire:click="$set('activeCuisineFilter', 'rajasthani')" class="px-4 py-2 text-xs font-semibold rounded-full transition-all border {{ $activeCuisineFilter === 'rajasthani' ? 'bg-amber-600 border-amber-600 text-white' : 'bg-white hover:bg-stone-50 text-stone-600 dark:bg-stone-900 border-neutral-200 dark:border-stone-800 dark:text-stone-300' }}">
                                {{ __('Rajasthani Royal') }}
                            </button>
                            <button wire:click="$set('activeCuisineFilter', 'top_rated')" class="px-4 py-2 text-xs font-semibold rounded-full transition-all border {{ $activeCuisineFilter === 'top_rated' ? 'bg-amber-600 border-amber-600 text-white' : 'bg-white hover:bg-stone-50 text-stone-600 dark:bg-stone-900 border-neutral-200 dark:border-stone-800 dark:text-stone-300' }}">
                                <i class="fa-solid fa-star text-amber-500 mr-1"></i>{{ __('Top Rated') }}
                            </button>
                        </div>

                        <!-- Restaurant Cards Container -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-h-[600px] overflow-y-auto pr-2">
                            @forelse ($this->restaurants as $resto)
                                @php
                                    $isSelected = $this->selectedRestaurantId === $resto->id;
                                @endphp
                                <div 
                                    wire:click="selectRestaurant({{ $resto->id }})"
                                    class="cursor-pointer border rounded-3xl overflow-hidden transition-all flex flex-col justify-between hover:shadow-xl {{ $isSelected ? 'border-amber-600 ring-2 ring-amber-600/20 bg-amber-500/5 dark:bg-amber-500/10 shadow-lg shadow-amber-600/5' : 'border-neutral-200 dark:border-stone-800 bg-white dark:bg-stone-900' }}"
                                >
                                    <!-- Photo/Banner header with gradient overlay or image -->
                                    <div class="relative h-28 overflow-hidden bg-gradient-to-r from-amber-600 to-orange-700">
                                        @if ($resto->image_path)
                                            <img src="{{ asset('storage/' . $resto->image_path) }}" alt="{{ $resto->name }}" class="w-full h-full object-cover" />
                                        @endif
                                        <div class="absolute inset-0 bg-black/25"></div>
                                        <div class="absolute top-3 right-3 bg-white/95 dark:bg-stone-900/95 backdrop-blur-md px-2.5 py-1 rounded-full text-xs font-bold text-amber-600 flex items-center gap-1 shadow-sm">
                                            <i class="fa-solid fa-star"></i>
                                            <span>{{ number_format($resto->rating, 1) }}</span>
                                        </div>
                                        <div class="absolute bottom-3 left-4 text-white">
                                            <span class="inline-block bg-white/20 backdrop-blur-md text-white text-[9px] font-extrabold px-2 py-0.5 rounded-full uppercase tracking-wider">
                                                {{ $this->getCuisineTag($resto->name) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Body Info -->
                                    <div class="p-4 space-y-3">
                                        <div>
                                            <h3 class="font-bold text-stone-900 dark:text-stone-100 text-base flex items-center gap-1.5">
                                                <i class="fa-solid fa-utensils text-amber-600 text-xs"></i>
                                                {{ $resto->name }}
                                            </h3>
                                            <div class="flex gap-2 items-center text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                                <span>{{ __('₹') }}</span>
                                                <span>•</span>
                                                <span class="line-clamp-1"><i class="fa-solid fa-location-dot text-stone-400 mr-0.5"></i>{{ $resto->address }}</span>
                                            </div>
                                        </div>

                                        <!-- Available slots today -->
                                        <div class="space-y-1.5 pt-2 border-t border-neutral-100 dark:border-neutral-800/40">
                                            <div class="text-[10px] text-neutral-400 font-bold uppercase tracking-wider">{{ __('Available Slots Today') }}</div>
                                            <div class="flex flex-wrap gap-1.5">
                                                <button wire:click.stop="selectRestaurantAndSlot({{ $resto->id }}, '12:00')" class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-all {{ $isSelected && $bookingTime === '12:00' ? 'bg-amber-600 text-white border-amber-600' : 'bg-stone-50 hover:bg-stone-100 dark:bg-stone-850 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-350 border border-neutral-200/50 dark:border-stone-800' }}">12:00 PM</button>
                                                <button wire:click.stop="selectRestaurantAndSlot({{ $resto->id }}, '14:00')" class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-all {{ $isSelected && $bookingTime === '14:00' ? 'bg-amber-600 text-white border-amber-600' : 'bg-stone-50 hover:bg-stone-100 dark:bg-stone-850 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-350 border border-neutral-200/50 dark:border-stone-800' }}">2:00 PM</button>
                                                <button wire:click.stop="selectRestaurantAndSlot({{ $resto->id }}, '19:00')" class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-all {{ $isSelected && $bookingTime === '19:00' ? 'bg-amber-600 text-white border-amber-600' : 'bg-stone-50 hover:bg-stone-100 dark:bg-stone-850 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-350 border border-neutral-200/50 dark:border-stone-800' }}">7:00 PM</button>
                                                <button wire:click.stop="selectRestaurantAndSlot({{ $resto->id }}, '21:00')" class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-all {{ $isSelected && $bookingTime === '21:00' ? 'bg-amber-600 text-white border-amber-600' : 'bg-stone-50 hover:bg-stone-100 dark:bg-stone-850 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-350 border border-neutral-200/50 dark:border-stone-800' }}">9:00 PM</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-2 text-center py-12 text-neutral-400">
                                    <i class="fa-solid fa-magnifying-glass text-3xl mb-3 text-stone-300"></i>
                                    <p class="text-sm font-semibold">{{ __('No restaurants match your search query.') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Right: Booking Form (5 cols) -->
                <div class="lg:col-span-5">
                    <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-6">
                        <flux:heading size="lg">{{ __('Reservation Details') }}</flux:heading>

                        @if ($this->selectedRestaurantId === 0)
                            <div class="text-center py-16 text-neutral-400">
                                <i class="fa-solid fa-hand-pointer text-3xl mb-3 text-amber-600 animate-bounce"></i>
                                <p class="text-sm">{{ __('Please select a restaurant from the list to start your booking.') }}</p>
                            </div>
                        @else
                            @php
                                $selectedResto = Restaurant::find($this->selectedRestaurantId);
                            @endphp
                            <div class="bg-stone-50 dark:bg-stone-900/60 p-4 rounded-xl border border-neutral-200 dark:border-stone-850 space-y-3">
                                @if ($selectedResto?->image_path)
                                    <div class="w-full h-32 rounded-lg overflow-hidden border border-neutral-200/50 dark:border-stone-800/50">
                                        <img src="{{ asset('storage/' . $selectedResto->image_path) }}" alt="{{ $selectedResto->name }}" class="w-full h-full object-cover" />
                                    </div>
                                @endif
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-neutral-400 uppercase tracking-wider font-bold">{{ __('Booking At') }}</div>
                                        <div class="font-bold text-stone-900 dark:text-stone-50 text-base">{{ $selectedResto?->name }}</div>
                                    </div>
                                    <span class="text-sm font-semibold text-amber-600 bg-amber-500/10 px-2.5 py-1 rounded-full"><i class="fa-solid fa-star mr-1"></i>{{ number_format($selectedResto?->rating, 1) }}</span>
                                </div>
                                <div class="text-xs text-neutral-600 dark:text-neutral-400 space-y-1">
                                    <div class="flex items-center gap-1.5"><i class="fa-solid fa-location-dot text-amber-600 text-stone-400"></i>{{ $selectedResto?->address }}</div>
                                    <div class="flex items-center gap-1.5"><i class="fa-solid fa-phone text-amber-600 text-stone-400"></i>{{ $selectedResto?->phone ?: __('+91 Not Provided') }}</div>
                                </div>
                                <div class="pt-1.5 border-t border-neutral-200/50 dark:border-neutral-800/50 flex justify-between items-center">
                                    <span class="text-[10px] text-green-600 font-bold uppercase bg-green-500/10 px-2 py-0.5 rounded-full"><i class="fa-solid fa-circle text-[5px] mr-1 animate-pulse"></i>Open for Reservations</span>
                                    <a href="{{ route('restaurant.landing', ['id' => $selectedResto?->id]) }}" target="_blank" class="text-xs font-bold text-amber-600 hover:text-amber-700 flex items-center gap-1">
                                        <span>View Royal Menu</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                    </a>
                                </div>
                            </div>

                            <form wire:submit="makeBooking" class="space-y-5">
                                <!-- Visual Guest Count buttons selector -->
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">{{ __('Number of Guests') }}</label>
                                    <div class="grid grid-cols-5 gap-2">
                                        @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 10, 12] as $g)
                                            <button type="button" wire:click="$set('guestCount', {{ $g }})" class="py-2.5 text-xs font-bold rounded-xl border transition-all {{ $guestCount === $g ? 'bg-amber-600 text-white border-amber-600 shadow-md shadow-amber-600/10' : 'bg-white hover:bg-neutral-50 dark:bg-stone-900 border-neutral-200 dark:border-stone-850 text-stone-700 dark:text-stone-300' }}">
                                                {{ $g }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Date selection -->
                                <flux:input wire:model="bookingDate" :label="__('Reservation Date')" type="date" min="{{ today()->toDateString() }}" required />

                                <!-- Visual Reservation Time slot grid -->
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">{{ __('Reservation Time') }}</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        @php
                                            $times = [
                                                '12:00' => '12:00 PM',
                                                '13:00' => '1:00 PM',
                                                '14:00' => '2:00 PM',
                                                '17:00' => '5:00 PM',
                                                '18:00' => '6:00 PM',
                                                '19:00' => '7:00 PM',
                                                '20:00' => '8:00 PM',
                                                '21:00' => '9:00 PM',
                                                '22:00' => '10:00 PM'
                                            ];
                                        @endphp
                                        @foreach ($times as $val => $label)
                                            <button type="button" wire:click="$set('bookingTime', '{{ $val }}')" class="py-2.5 text-xs font-bold rounded-xl border transition-all {{ $bookingTime === $val ? 'bg-amber-600 text-white border-amber-600 shadow-md' : 'bg-white hover:bg-neutral-50 dark:bg-stone-900 border-neutral-200 dark:border-stone-850 text-stone-700 dark:text-stone-350' }}">
                                                {{ $label }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Modern Payment Type Selector cards -->
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">{{ __('Payment Option') }}</label>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div 
                                            wire:click="$set('paymentType', 'advance')"
                                            class="cursor-pointer border rounded-2xl p-4 transition-all flex flex-col justify-between gap-1 {{ $paymentType === 'advance' ? 'border-amber-600 ring-2 ring-amber-600/20 bg-amber-500/5 dark:bg-amber-500/10' : 'border-neutral-200 dark:border-stone-800 bg-white dark:bg-stone-900 hover:bg-neutral-50 dark:hover:bg-stone-855' }}"
                                        >
                                            <div class="font-bold text-sm text-stone-800 dark:text-stone-200">{{ __('Advance Deposit') }}</div>
                                            <div class="text-xs text-neutral-500">{{ __('₹1,250.00 holding fee') }}</div>
                                        </div>

                                        <div 
                                            wire:click="$set('paymentType', 'full')"
                                            class="cursor-pointer border rounded-2xl p-4 transition-all flex flex-col justify-between gap-1 {{ $paymentType === 'full' ? 'border-amber-600 ring-2 ring-amber-600/20 bg-amber-500/5 dark:bg-amber-500/10' : 'border-neutral-200 dark:border-stone-800 bg-white dark:bg-stone-900 hover:bg-neutral-50 dark:hover:bg-stone-855' }}"
                                        >
                                            <div class="font-bold text-sm text-stone-800 dark:text-stone-200">{{ __('Full Payment') }}</div>
                                            <div class="text-xs text-neutral-500">{{ __('₹4,999.00 pre-pay') }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <flux:textarea wire:model="notes" :label="__('Special Requests / Notes')" placeholder="E.g., Maharaja seat preference, birthday celebration, allergy notices." />

                                <!-- Cost Summary Breakdown box -->
                                <div class="bg-amber-500/5 border border-amber-900/10 dark:border-amber-100/10 rounded-2xl p-4 space-y-2">
                                    <div class="flex justify-between text-xs text-stone-600 dark:text-stone-400">
                                        <span>{{ __('Selected Option:') }}</span>
                                        <span class="font-bold uppercase tracking-wider">{{ $paymentType === 'advance' ? __('Advance Deposit') : __('Full Reservation Payment') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm items-center pt-1 border-t border-neutral-200/50 dark:border-stone-800/50">
                                        <span class="font-bold text-stone-850 dark:text-stone-200">{{ __('Amount Due Now:') }}</span>
                                        <span class="text-xl font-extrabold text-amber-600">₹{{ number_format($paymentType === 'advance' ? 1250 : 4999, 2) }}</span>
                                    </div>
                                </div>

                                <flux:button variant="primary" type="submit" class="w-full mt-4 bg-amber-600 hover:bg-amber-700 text-white font-bold shadow-md shadow-amber-600/20">
                                    {{ __('Proceed to Checkout') }}
                                </flux:button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

        @elseif ($tab === 'history')
            <!-- Bottom Section: Booking History -->
            <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-neutral-100 dark:border-stone-800 pb-4">
                    <div>
                        <flux:heading size="lg">{{ __('My Reservations') }}</flux:heading>
                        <flux:subheading class="text-xs">{{ __('Track, review, or cancel your active restaurant slots.') }}</flux:subheading>
                    </div>
                    
                    <!-- Filter Tabs with horizontal scrollbar on mobile -->
                    <div class="flex bg-stone-100 dark:bg-stone-800 p-1 rounded-xl text-xs gap-1 max-w-full overflow-x-auto whitespace-nowrap scrollbar-none">
                        <button wire:click="$set('historyFilter', 'all')" class="px-3 py-1.5 rounded-lg font-semibold transition-all {{ $historyFilter === 'all' ? 'bg-white dark:bg-stone-900 shadow-sm text-stone-900 dark:text-stone-50 font-bold' : 'text-stone-500 hover:text-stone-700' }}">{{ __('All') }}</button>
                        <button wire:click="$set('historyFilter', 'pending')" class="px-3 py-1.5 rounded-lg font-semibold transition-all {{ $historyFilter === 'pending' ? 'bg-white dark:bg-stone-900 shadow-sm text-stone-900 dark:text-stone-50 font-bold' : 'text-stone-500 hover:text-stone-700' }}">{{ __('Pending') }}</button>
                        <button wire:click="$set('historyFilter', 'confirmed')" class="px-3 py-1.5 rounded-lg font-semibold transition-all {{ $historyFilter === 'confirmed' ? 'bg-white dark:bg-stone-900 shadow-sm text-stone-900 dark:text-stone-50 font-bold' : 'text-stone-500 hover:text-stone-700' }}">{{ __('Confirmed') }}</button>
                        <button wire:click="$set('historyFilter', 'completed_cancelled')" class="px-3 py-1.5 rounded-lg font-semibold transition-all {{ $historyFilter === 'completed_cancelled' ? 'bg-white dark:bg-stone-900 shadow-sm text-stone-900 dark:text-stone-50 font-bold' : 'text-stone-500 hover:text-stone-700' }}">{{ __('Completed/Cancelled') }}</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px] text-left border-collapse">
                        <thead>
                            <tr class="border-b border-neutral-100 dark:border-neutral-800 text-sm">
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Restaurant') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Date / Time') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Guests') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Payment') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Status') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-50 dark:divide-stone-800/40">
                            @forelse ($this->myBookings as $b)
                                <tr class="text-sm hover:bg-neutral-50/50 dark:hover:bg-stone-800/10 transition-colors">
                                    <td class="py-4 font-bold text-neutral-800 dark:text-neutral-200">
                                        <div>{{ $b->restaurant->name }}</div>
                                        <div class="text-xs text-neutral-500 font-medium mt-0.5">{{ $b->restaurant->phone }}</div>
                                        @if ($b->notes)
                                            <div class="mt-1 flex items-start gap-1 text-[11px] text-amber-600 dark:text-amber-400 bg-amber-500/5 dark:bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/10 max-w-xs">
                                                <flux:icon name="chat-bubble-bottom-center-text" class="size-3.5 shrink-0 mt-0.5" />
                                                <span class="break-words">{{ $b->notes }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-4">
                                        <div class="font-bold text-neutral-800 dark:text-neutral-200">{{ $b->booking_date->format('M d, Y') }}</div>
                                        <div class="text-xs text-neutral-500 mt-0.5 font-mono">{{ \Carbon\Carbon::parse($b->booking_time)->format('h:i A') }}</div>
                                    </td>
                                    <td class="py-4 text-center font-extrabold text-neutral-850 dark:text-neutral-200">
                                        {{ $b->guest_count }}
                                    </td>
                                    <td class="py-4 text-center">
                                        <div class="font-bold text-neutral-800 dark:text-neutral-200">₹{{ number_format($b->payment_amount, 2) }}</div>
                                        <div class="mt-1 flex justify-center">
                                            <flux:badge color="{{ str_contains($b->payment_status, 'paid') ? 'green' : 'amber' }}" size="xs" class="font-bold uppercase text-[8px] tracking-wide">
                                                {{ $b->payment_status }}
                                            </flux:badge>
                                        </div>
                                    </td>
                                    <td class="py-4 text-center">
                                        <div class="flex justify-center">
                                            <flux:badge color="{{ $b->status === 'confirmed' ? 'green' : ($b->status === 'pending' ? 'amber' : ($b->status === 'cancelled' ? 'stone' : 'red')) }}" class="font-bold uppercase text-[9px] px-2">
                                                {{ $b->status }}
                                            </flux:badge>
                                        </div>
                                    </td>
                                    <td class="py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if (in_array($b->status, ['pending', 'confirmed']))
                                                <flux:button 
                                                    size="xs" 
                                                    variant="danger" 
                                                    wire:click="cancelBooking({{ $b->id }})"
                                                    wire:confirm="{{ __('Are you sure you want to cancel this booking?') }}"
                                                    class="font-bold py-1 px-3 border-none cursor-pointer"
                                                >
                                                    {{ __('Cancel') }}
                                                </flux:button>
                                            @endif
                                            @if (str_contains($b->payment_status, 'paid'))
                                                <flux:button size="xs" variant="ghost" icon="document-text" as="a" href="{{ route('booking.invoice', ['id' => $b->id]) }}" target="_blank" class="border border-stone-200 dark:border-stone-800 cursor-pointer">
                                                    {{ __('Invoice') }}
                                                </flux:button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-neutral-500">
                                        <flux:icon name="calendar" class="size-8 mx-auto mb-2 text-neutral-300" />
                                        <p class="text-sm font-semibold">{{ __('No reservations found.') }}</p>
                                        <p class="text-xs text-neutral-400 mt-1">{{ __('Try selecting a different filter or make a new booking.') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-4 border-t border-neutral-100 dark:border-stone-800">
                    {{ $this->myBookings->links() }}
                </div>
            </div>
        @endif
    </div>
