<?php

use Livewire\Component;
use App\Models\Booking;
use App\Models\Attendance;
use App\Models\Table;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Flux\Flux;

new class extends Component {
    use WithPagination;

    #[Url(keep: true)]
    public string $tab = 'dashboard';

    public string $searchBooking = '';
    public string $searchMenu = '';
    public string $notes = '';

    public function updatingSearchBooking(): void
    {
        $this->resetPage('bookingsPage');
    }

    public function updatingSearchMenu(): void
    {
        $this->resetPage('menuPage');
    }

    public function checkIn(): void
    {
        $user = Auth::user();
        $restaurantId = $user->restaurant_id;

        // Check if already checked in today without checking out
        $activeShift = Attendance::where('user_id', $user->id)
            ->whereNull('check_out')
            ->first();

        if ($activeShift) {
            Flux::toast(variant: 'warning', text: __('You are already checked in.'));
            return;
        }

        Attendance::create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurantId,
            'check_in' => now(),
            'notes' => $this->notes,
        ]);

        Log::info("[Restaurant ID: {$restaurantId}] Staff member '{$user->name}' (ID: {$user->id}) checked in for shift.");

        $this->reset('notes');
        Flux::toast(variant: 'success', text: __('Shift check-in successful.'));
    }

    public function checkOut(): void
    {
        $user = Auth::user();

        $activeShift = Attendance::where('user_id', $user->id)
            ->whereNull('check_out')
            ->first();

        if (! $activeShift) {
            Flux::toast(variant: 'danger', text: __('No active shift found to check out of.'));
            return;
        }

        $activeShift->update([
            'check_out' => now(),
        ]);

        Log::info("[Restaurant ID: {$user->restaurant_id}] Staff member '{$user->name}' (ID: {$user->id}) checked out of shift.");

        Flux::toast(variant: 'success', text: __('Shift check-out successful. Have a good rest!'));
    }

    public function updateBookingStatus(int $id, string $status): void
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => $status]);

        Log::info("[Restaurant ID: {$booking->restaurant_id}] Staff member '" . Auth::user()->name . "' (ID: " . Auth::id() . ") updated booking ID {$id} status to: {$status}.");

        // Send Notification
        try {
            $booking->user->notify(new \App\Notifications\BookingStatusNotification($booking, $status));
        } catch (\Exception $e) {
            logger()->error('Notification failed: ' . $e->getMessage());
        }

        Flux::toast(variant: 'success', text: __('Booking status updated.'));
    }

    #[Computed]
    public function currentShift(): ?Attendance
    {
        return Attendance::where('user_id', Auth::id())
            ->whereNull('check_out')
            ->first();
    }

    #[Computed]
    public function todayShifts(): \Illuminate\Database\Eloquent\Collection
    {
        return Attendance::where('user_id', Auth::id())
            ->whereDate('check_in', now()->toDateString())
            ->latest()
            ->get();
    }

    #[Computed]
    public function shiftsHistory(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Attendance::where('user_id', Auth::id())
            ->latest()
            ->paginate(10, ['*'], 'shiftsPage');
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
            ->orderBy('booking_date', 'asc')
            ->orderBy('booking_time', 'asc')
            ->paginate(10, ['*'], 'bookingsPage');
    }

    #[Computed]
    public function tablesList(): \Illuminate\Database\Eloquent\Collection
    {
        $restaurantId = Auth::user()->restaurant_id;
        return Table::where('restaurant_id', $restaurantId)->orderBy('name')->get();
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
}; ?>

    <div class="space-y-8 p-6 max-w-7xl mx-auto transition-all duration-300">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-neutral-100 dark:border-stone-800 pb-6">
            <div>
                @if ($tab === 'dashboard')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-teal-600 to-indigo-600 dark:from-teal-400 dark:to-indigo-400 bg-clip-text text-transparent">{{ __('Staff Workspace') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Mark shifts, manage floor bookings, and update reservation status for ' . Auth::user()->restaurant->name) }}</flux:subheading>
                @elseif ($tab === 'shifts')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-emerald-600 to-teal-600 dark:from-emerald-400 dark:to-teal-400 bg-clip-text text-transparent">{{ __('Shift Tracker & Console') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Check in/out of duty, write session notes, and audit your shift logs') }}</flux:subheading>
                @elseif ($tab === 'bookings')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-sky-600 to-indigo-600 dark:from-sky-400 dark:to-indigo-400 bg-clip-text text-transparent">{{ __('Floor Bookings Console') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('View assigned table dining configurations and manage seating guest statuses') }}</flux:subheading>
                @elseif ($tab === 'tables')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-teal-600 to-emerald-600 dark:from-teal-400 dark:to-emerald-400 bg-clip-text text-transparent">{{ __('Floor Tables Layout') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Monitor dining tables status, capacities, and active configurations') }}</flux:subheading>
                @elseif ($tab === 'menu')
                    <flux:heading size="xl" level="1" class="font-black bg-gradient-to-r from-amber-600 to-orange-600 dark:from-amber-400 dark:to-orange-400 bg-clip-text text-transparent">{{ __('Bistro Menu Directory') }}</flux:heading>
                    <flux:subheading class="text-neutral-500 dark:text-neutral-400 mt-1">{{ __('View active culinary dishes, categories, pricing, and stock status') }}</flux:subheading>
                @endif
            </div>
            <div>
                <flux:badge color="teal" class="text-sm font-bold px-3 py-1 shadow-sm uppercase tracking-wider bg-teal-50 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-900/50">{{ __('Staff Member') }}</flux:badge>
            </div>
        </div>

        @if ($tab === 'dashboard')
            <!-- Overview KPI Cards & Quick Snapshots -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Shift Status Card -->
                <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs relative overflow-hidden group hover:shadow-md transition-all duration-300 cursor-pointer" wire:click="$set('tab', 'shifts')">
                    <div class="absolute right-0 top-0 size-24 bg-teal-500/5 dark:bg-teal-500/10 rounded-bl-full pointer-events-none"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:text size="sm" class="text-neutral-500 dark:text-neutral-400 font-medium tracking-wide uppercase">{{ __('Shift Status') }}</flux:text>
                            <div class="mt-2">
                                @if ($this->currentShift)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/25">
                                        <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        {{ __('On Duty') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/25">
                                        <span class="size-2 rounded-full bg-rose-500"></span>
                                        {{ __('Off Duty') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="p-3 bg-teal-500/10 dark:bg-teal-500/20 text-teal-600 dark:text-teal-400 rounded-xl">
                            <flux:icon name="clock" class="size-6" />
                        </div>
                    </div>
                </div>

                <!-- Today's Shift Count -->
                <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs relative overflow-hidden group hover:shadow-md transition-all duration-300">
                    <div class="absolute right-0 top-0 size-24 bg-indigo-500/5 dark:bg-indigo-500/10 rounded-bl-full pointer-events-none"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:text size="sm" class="text-neutral-500 dark:text-neutral-400 font-medium tracking-wide uppercase">{{ __('Today\'s Shift Count') }}</flux:text>
                            <flux:heading size="xl" class="mt-2 font-black text-neutral-800 dark:text-neutral-100">{{ $this->todayShifts->count() }} {{ __('Shifts') }}</flux:heading>
                        </div>
                        <div class="p-3 bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-xl">
                            <flux:icon name="calendar-days" class="size-6" />
                        </div>
                    </div>
                </div>

                <!-- Total Bookings Assigned -->
                <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs relative overflow-hidden group hover:shadow-md transition-all duration-300 cursor-pointer" wire:click="$set('tab', 'bookings')">
                    <div class="absolute right-0 top-0 size-24 bg-sky-500/5 dark:bg-sky-500/10 rounded-bl-full pointer-events-none"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:text size="sm" class="text-neutral-500 dark:text-neutral-400 font-medium tracking-wide uppercase">{{ __('Total Reservations') }}</flux:text>
                            <flux:heading size="xl" class="mt-2 font-black text-neutral-800 dark:text-neutral-100">{{ $this->bookings->total() }} {{ __('Bookings') }}</flux:heading>
                        </div>
                        <div class="p-3 bg-sky-500/10 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400 rounded-xl">
                            <flux:icon name="document-text" class="size-6" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Snapshot Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Today's Reservations snapshot -->
                <div class="lg:col-span-2 bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-neutral-100 dark:border-stone-800 pb-3">
                        <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Reservations Snapshot') }}</flux:heading>
                        <flux:button variant="ghost" size="xs" wire:click="$set('tab', 'bookings')" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                            {{ __('View All') }} &rarr;
                        </flux:button>
                    </div>

                    <div class="divide-y divide-neutral-150 dark:divide-stone-850">
                        @forelse ($this->bookings->take(5) as $booking)
                            <div class="flex items-center justify-between py-3">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-lg">
                                        <flux:icon name="calendar" class="size-5" />
                                    </div>
                                    <div>
                                        <div class="font-bold text-neutral-800 dark:text-neutral-200">{{ $booking->user->name }}</div>
                                        <div class="text-xs text-neutral-500">
                                            {{ $booking->guest_count }} {{ __('Guests') }} &bull; {{ $booking->table ? $booking->table->name : 'N/A' }}
                                        </div>
                                        @if ($booking->notes)
                                            <div class="mt-1 flex items-start gap-1 text-xs text-amber-600 dark:text-amber-400 bg-amber-500/5 dark:bg-amber-500/10 px-2 py-0.5 rounded-md border border-amber-500/10 max-w-md">
                                                <flux:icon name="chat-bubble-bottom-center-text" class="size-3 shrink-0 mt-0.5" />
                                                <span>{{ $booking->notes }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">{{ \Carbon\Carbon::parse($booking->booking_time)->format('h:i A') }}</div>
                                    <flux:badge size="xs" color="{{ $booking->status === 'confirmed' ? 'green' : ($booking->status === 'pending' ? 'amber' : 'red') }}" class="font-semibold uppercase tracking-wider text-[8px]">
                                        {{ $booking->status }}
                                    </flux:badge>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-neutral-500 text-sm">
                                {{ __('No reservations scheduled.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Today's Shift tracking snapshot -->
                <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-4 self-start">
                    <div class="flex items-center justify-between border-b border-neutral-100 dark:border-stone-800 pb-3">
                        <flux:heading size="md" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Duty Hours Today') }}</flux:heading>
                        <flux:button variant="ghost" size="xs" wire:click="$set('tab', 'shifts')" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                            {{ __('Shift details') }}
                        </flux:button>
                    </div>

                    <div class="space-y-3">
                        @forelse ($this->todayShifts as $shift)
                            <div class="flex items-center justify-between text-sm py-2">
                                <div>
                                    <div class="font-semibold text-neutral-800 dark:text-neutral-200">
                                        {{ $shift->check_in->format('h:i A') }} - {{ $shift->check_out ? $shift->check_out->format('h:i A') : 'Active' }}
                                    </div>
                                    <div class="text-xs text-neutral-500 truncate max-w-[150px]">
                                        {{ $shift->notes ?: __('No notes') }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if ($shift->check_out)
                                        <flux:badge size="xs" color="zinc" class="font-bold">{{ $shift->check_in->diffAsCarbonInterval($shift->check_out)->forHumans(['short' => true]) }}</flux:badge>
                                    @else
                                        <span class="text-[10px] text-green-500 font-bold uppercase tracking-wider animate-pulse">{{ __('On Duty') }}</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 text-xs text-neutral-400">{{ __('No shifts recorded today.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>

        @elseif ($tab === 'shifts')
            <!-- Shift tracker full screen -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Shift tracker control card -->
                <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs h-fit space-y-4">
                    <div>
                        <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Shift Punch Console') }}</flux:heading>
                        <flux:subheading class="text-xs">{{ __('Punch in/out for shift check-ins and session notes') }}</flux:subheading>
                    </div>

                    @if ($this->currentShift)
                        <div class="bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-800/40 rounded-2xl p-5 text-center space-y-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400">
                                <span class="size-2 bg-green-500 rounded-full animate-ping"></span>
                                {{ __('Shift active') }}
                            </span>
                            <div class="text-xs text-neutral-500">
                                {{ __('Started at:') }} {{ $this->currentShift->check_in->format('h:i A') }} ({{ $this->currentShift->check_in->diffForHumans() }})
                            </div>
                            @if ($this->currentShift->notes)
                                <div class="text-xs text-neutral-600 dark:text-neutral-400 bg-white dark:bg-stone-950 p-3 border border-neutral-100 dark:border-stone-900 rounded-xl italic font-medium">
                                    "{{ $this->currentShift->notes }}"
                                </div>
                            @endif

                            <flux:button variant="danger" class="w-full mt-4 font-black py-2.5 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white rounded-xl shadow-lg shadow-red-500/20 hover:shadow-red-500/30 transition-all duration-200 border-none cursor-pointer" wire:click="checkOut">
                                {{ __('Go Off Duty (Punch Out)') }}
                            </flux:button>
                        </div>
                    @else
                        <div class="space-y-4 pt-2">
                            <flux:textarea
                                wire:model="notes"
                                :label="__('Punch-in Notes')"
                                placeholder="E.g., Dinner shift service, table service duty."
                                rows="3"
                             />
                            <flux:button variant="primary" class="w-full mt-2 font-black py-2.5 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white rounded-xl shadow-lg shadow-teal-500/20 hover:shadow-teal-500/30 transition-all duration-200 border-none cursor-pointer" wire:click="checkIn">
                                {{ __('Go On Duty (Punch In)') }}
                            </flux:button>
                        </div>
                    @endif
                </div>

                <!-- Shift Records Table -->
                <div class="lg:col-span-2 bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-6">
                    <div>
                        <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Historical Shift Records') }}</flux:heading>
                        <flux:subheading class="text-xs">{{ __('Verify historical attendances and shift session durations') }}</flux:subheading>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[600px] text-left border-collapse">
                            <thead>
                                <tr class="border-b border-neutral-100 dark:border-stone-800 text-sm">
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Date') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Punch In') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Punch Out') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-right">{{ __('Shift Duration') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-50 dark:divide-stone-800/40">
                                @forelse ($this->shiftsHistory as $shift)
                                    <tr class="hover:bg-neutral-50/50 dark:hover:bg-stone-800/20 transition-colors">
                                        <td class="py-4">
                                            <div class="font-bold text-neutral-850 dark:text-neutral-200 text-sm">{{ $shift->check_in->format('M d, Y') }}</div>
                                            @if($shift->notes)
                                                <div class="text-[10px] text-neutral-400 italic max-w-xs truncate">{{ $shift->notes }}</div>
                                            @endif
                                        </td>
                                        <td class="py-4 text-center text-xs text-neutral-700 dark:text-neutral-300 font-mono">
                                            {{ $shift->check_in->format('h:i A') }}
                                        </td>
                                        <td class="py-4 text-center text-xs text-neutral-700 dark:text-neutral-300 font-mono">
                                            {{ $shift->check_out ? $shift->check_out->format('h:i A') : '-' }}
                                        </td>
                                        <td class="py-4 text-right">
                                            @if ($shift->check_out)
                                                <span class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">{{ $shift->check_in->diffAsCarbonInterval($shift->check_out)->forHumans(['short' => true]) }}</span>
                                            @else
                                                <span class="text-xs text-green-500 animate-pulse font-black uppercase tracking-wider">{{ __('On Duty') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-12 text-center text-neutral-500 text-sm">
                                            {{ __('No shifts records found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-4 border-t border-neutral-100 dark:border-stone-800">
                        {{ $this->shiftsHistory->links() }}
                    </div>
                </div>
            </div>

        @elseif ($tab === 'bookings')
            <!-- Floor Bookings Console -->
            <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Floor Seating & Bookings') }}</flux:heading>
                        <flux:subheading class="text-xs">{{ __('Manage reservation statuses, seat assignments, and track live guests') }}</flux:subheading>
                    </div>
                    <!-- Search Filter -->
                    <div class="w-full sm:w-72">
                        <flux:input wire:model.live.debounce.150ms="searchBooking" icon="magnifying-glass" placeholder="Search customer or email..." />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[750px] text-left border-collapse">
                        <thead>
                            <tr class="border-b border-neutral-100 dark:border-stone-800 text-sm">
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Time') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Customer/Table') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Guests') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Status') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-right">{{ __('Status controls') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-50 dark:divide-stone-800/40">
                            @forelse ($this->bookings as $booking)
                                <tr class="hover:bg-neutral-50/50 dark:hover:bg-stone-800/20 transition-colors">
                                    <td class="py-4">
                                        <div class="font-bold text-neutral-800 dark:text-neutral-200">{{ $booking->booking_date->format('M d, Y') }}</div>
                                        <div class="text-xs text-neutral-500 font-mono">{{ \Carbon\Carbon::parse($booking->booking_time)->format('h:i A') }}</div>
                                    </td>
                                    <td class="py-4">
                                        <div class="font-bold text-neutral-800 dark:text-neutral-200">{{ $booking->user->name }}</div>
                                        <div class="text-xs text-neutral-500 font-medium">Table: {{ $booking->table ? $booking->table->name : 'N/A' }}</div>
                                        @if ($booking->notes)
                                            <div class="mt-1.5 flex items-start gap-1.5 text-xs text-amber-600 dark:text-amber-400 bg-amber-500/5 dark:bg-amber-500/10 px-2.5 py-1 rounded-md border border-amber-500/10 max-w-sm">
                                                <flux:icon name="chat-bubble-bottom-center-text" class="size-4 shrink-0 text-amber-500 dark:text-amber-400 mt-0.5" />
                                                <span class="break-words">{{ $booking->notes }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-4 text-center font-extrabold text-neutral-850 dark:text-neutral-200">
                                        {{ $booking->guest_count }}
                                    </td>
                                    <td class="py-4 text-center">
                                        <div class="flex justify-center">
                                            <flux:badge color="{{ $booking->status === 'confirmed' ? 'green' : ($booking->status === 'pending' ? 'amber' : 'red') }}" class="font-bold uppercase text-[9px] tracking-wide px-2">
                                                {{ $booking->status }}
                                            </flux:badge>
                                        </div>
                                    </td>
                                    <td class="py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if ($booking->status === 'pending')
                                                <flux:button size="xs" variant="primary" class="bg-green-600 hover:bg-green-700 text-white font-bold py-1 px-3 rounded-lg border-none cursor-pointer" wire:click="updateBookingStatus({{ $booking->id }}, 'confirmed')">{{ __('Confirm') }}</flux:button>
                                            @elseif ($booking->status === 'confirmed')
                                                <flux:button size="xs" variant="ghost" class="border border-stone-200 dark:border-stone-800 font-semibold cursor-pointer" wire:click="updateBookingStatus({{ $booking->id }}, 'completed')">{{ __('Mark Completed') }}</flux:button>
                                            @endif
                                            <flux:button size="xs" variant="danger" class="font-bold py-1 px-3 rounded-lg border-none cursor-pointer" wire:click="updateBookingStatus({{ $booking->id }}, 'cancelled')">{{ __('Cancel') }}</flux:button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-neutral-500">
                                        {{ __('No floor reservations scheduled.') }}
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

        @elseif ($tab === 'tables')
            <!-- Read-only Floor Tables Directory -->
            <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-6">
                <div>
                    <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Tables Layout Directory') }}</flux:heading>
                    <flux:subheading class="text-xs">{{ __('View physical dining tables layout, capacities, and active availability status') }}</flux:subheading>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @forelse ($this->tablesList as $t)
                        <div class="bg-neutral-50/40 dark:bg-stone-950/20 border border-neutral-200/80 dark:border-stone-850 rounded-2xl p-5 flex flex-col justify-between gap-4 group hover:shadow-sm transition-all duration-300">
                            <div class="flex justify-between items-start">
                                <div class="space-y-1">
                                    <div class="font-black text-neutral-800 dark:text-neutral-100 text-base">{{ $t->name }}</div>
                                    <div class="text-xs text-neutral-500 flex items-center gap-1.5">
                                        <flux:icon name="users" class="size-4 text-neutral-400" />
                                        <span>{{ $t->capacity }} {{ __('seats capacity') }}</span>
                                    </div>
                                </div>
                                <flux:badge size="xs" color="{{ $t->status === 'available' ? 'green' : 'red' }}" class="font-bold tracking-wide uppercase text-[8px] px-2 py-0.5">
                                    {{ $t->status === 'available' ? __('Active') : __('Out of Order') }}
                                </flux:badge>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center text-neutral-500 text-sm">{{ __('No floor seating tables found.') }}</div>
                    @endforelse
                </div>
            </div>

        @elseif ($tab === 'menu')
            <!-- Read-only Bistro Menu Directory -->
            <div class="bg-white dark:bg-stone-900 border border-neutral-200/80 dark:border-stone-800 rounded-2xl p-6 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <flux:heading size="lg" class="font-bold text-neutral-800 dark:text-neutral-100">{{ __('Bistro Menu Directory') }}</flux:heading>
                        <flux:subheading class="text-xs">{{ __('Browse current culinary offerings, categories, pricing, and stock status') }}</flux:subheading>
                    </div>
                    <!-- Search input -->
                    <div class="w-full sm:w-72">
                        <flux:input wire:model.live.debounce.150ms="searchMenu" icon="magnifying-glass" placeholder="Search menu dishes..." />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[650px] text-left border-collapse">
                        <thead>
                            <tr class="border-b border-neutral-100 dark:border-stone-800 text-sm">
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Dish') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Category') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Price') }}</th>
                                <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-right">{{ __('Availability') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-50 dark:divide-stone-800/40">
                            @forelse ($this->menuItemsList as $item)
                                <tr class="hover:bg-neutral-50/50 dark:hover:bg-stone-800/20 transition-colors">
                                    <td class="py-4">
                                        <div class="font-bold text-neutral-800 dark:text-neutral-200">{{ $item->name }}</div>
                                        @if($item->description)
                                            <div class="text-xs text-neutral-500 max-w-md mt-0.5">{{ $item->description }}</div>
                                        @endif
                                    </td>
                                    <td class="py-4 text-center">
                                        <flux:badge size="xs" color="indigo" class="font-bold tracking-wide uppercase text-[8px]">{{ $item->category }}</flux:badge>
                                    </td>
                                    <td class="py-4 text-center font-bold text-neutral-800 dark:text-neutral-200">
                                        ₹{{ number_format($item->price, 2) }}
                                    </td>
                                    <td class="py-4 text-right">
                                        <flux:badge color="{{ $item->is_available ? 'green' : 'red' }}" class="font-bold tracking-wide uppercase text-[8px] px-2 py-0.5">
                                            {{ $item->is_available ? __('In Stock') : __('Out of Stock') }}
                                        </flux:badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-neutral-500">
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
        @endif
    </div>
