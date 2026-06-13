<?php

use Livewire\Component;
use App\Models\Booking;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Flux\Flux;

new class extends Component {
    public string $notes = '';

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

        Flux::toast(variant: 'success', text: __('Shift check-out successful. Have a good rest!'));
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
    public function bookings(): \Illuminate\Database\Eloquent\Collection
    {
        $restaurantId = Auth::user()->restaurant_id;
        return Booking::where('restaurant_id', $restaurantId)
            ->with(['user', 'table'])
            ->whereDate('booking_date', '>=', now()->toDateString())
            ->orderBy('booking_date', 'asc')
            ->orderBy('booking_time', 'asc')
            ->get();
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
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('Staff Workspace') }}</flux:heading>
                <flux:subheading>{{ __('Mark shifts, manage floor bookings, and update reservation status for ' . Auth::user()->restaurant->name) }}</flux:subheading>
            </div>
            <flux:badge color="teal" class="text-sm font-semibold uppercase">{{ __('Restaurant Staff') }}</flux:badge>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Side: Attendance Control -->
            <div class="space-y-6" id="shift-console">
                <!-- Check-in/out console -->
                <div class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs">
                    <flux:heading size="lg" class="mb-4">{{ __('Shift Tracker') }}</flux:heading>

                    @if ($this->currentShift)
                        <div class="bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-800/40 rounded-lg p-4 text-center space-y-4 mb-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400">
                                <span class="size-1.5 rounded-full bg-green-500 animate-ping"></span>
                                {{ __('Currently on shift') }}
                            </span>
                            <div class="text-xs text-neutral-500">
                                {{ __('Started at:') }} {{ $this->currentShift->check_in->format('H:i A') }} ({{ $this->currentShift->check_in->diffForHumans() }})
                            </div>
                            @if ($this->currentShift->notes)
                                <div class="text-xs text-neutral-600 dark:text-neutral-400 italic">
                                    "{{ $this->currentShift->notes }}"
                                </div>
                            @endif

                            <flux:button variant="danger" class="w-full font-semibold" wire:click="checkOut">
                                {{ __('Check Out of Shift') }}
                            </flux:button>
                        </div>
                    @else
                        <div class="space-y-4">
                            <flux:textarea
                                wire:model="notes"
                                :label="__('Shift Check-in Notes')"
                                placeholder="E.g., Opening staff shift, floor duty."
                                class="text-sm"
                             />
                            <flux:button variant="primary" class="w-full font-semibold bg-teal-600 hover:bg-teal-700" wire:click="checkIn">
                                {{ __('Check In for Shift') }}
                            </flux:button>
                        </div>
                    @endif
                </div>

                <!-- Today's logs -->
                <div class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs">
                    <flux:heading size="md" class="mb-4">{{ __('Today\'s Shift Records') }}</flux:heading>
                    <div class="space-y-3">
                        @forelse ($this->todayShifts as $shift)
                            <div class="border-b border-neutral-100 dark:border-neutral-800 pb-2 text-sm flex justify-between items-center">
                                <div>
                                    <div class="font-medium text-neutral-800 dark:text-neutral-200">
                                        {{ $shift->check_in->format('H:i A') }} - {{ $shift->check_out ? $shift->check_out->format('H:i A') : 'Active' }}
                                    </div>
                                    <div class="text-xs text-neutral-500">
                                        {{ $shift->notes ?: __('No notes') }}
                                    </div>
                                </div>
                                <div class="font-bold text-neutral-600 dark:text-neutral-400">
                                    @if ($shift->check_out)
                                        {{ $shift->check_in->diffAsCarbonInterval($shift->check_out)->forHumans(['short' => true]) }}
                                    @else
                                        <span class="text-green-500 animate-pulse">{{ __('On Duty') }}</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <flux:text class="text-neutral-400 text-center py-4">{{ __('No shifts recorded today.') }}</flux:text>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Side: Floor Bookings -->
            <div class="lg:col-span-2 space-y-6" id="assigned-bookings">
                <div class="bg-white dark:bg-stone-900 border border-neutral-200 dark:border-stone-800 rounded-xl p-6 shadow-xs">
                    <flux:heading size="lg" class="mb-4">{{ __('Floor Reservations') }}</flux:heading>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-neutral-100 dark:border-neutral-800">
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Time') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Customer/Table') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Guests') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-center">{{ __('Status') }}</th>
                                    <th class="py-3 font-semibold text-neutral-500 dark:text-neutral-400 text-right">{{ __('Status controls') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->bookings as $booking)
                                    <tr class="border-b border-neutral-100 dark:border-neutral-800 text-sm hover:bg-neutral-50/50">
                                        <td class="py-3">
                                            <div class="font-medium">{{ $booking->booking_date->format('M d') }}</div>
                                            <div class="text-xs text-neutral-500">{{ \Carbon\Carbon::parse($booking->booking_time)->format('h:i A') }}</div>
                                        </td>
                                        <td class="py-3">
                                            <div class="font-medium text-neutral-800 dark:text-neutral-200">{{ $booking->user->name }}</div>
                                            <div class="text-xs text-neutral-500">{{ __('Table:') }} {{ $booking->table ? $booking->table->name : 'N/A' }}</div>
                                        </td>
                                        <td class="py-3 text-center font-bold">
                                            {{ $booking->guest_count }}
                                        </td>
                                        <td class="py-3 text-center">
                                            <flux:badge size="xs" color="{{ $booking->status === 'confirmed' ? 'green' : ($booking->status === 'pending' ? 'amber' : 'red') }}">
                                                {{ $booking->status }}
                                            </flux:badge>
                                        </td>
                                        <td class="py-3 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                @if ($booking->status === 'pending')
                                                    <flux:button size="xs" variant="primary" class="bg-green-600 hover:bg-green-700 text-white" wire:click="updateBookingStatus({{ $booking->id }}, 'confirmed')">{{ __('Confirm') }}</flux:button>
                                                @elseif ($booking->status === 'confirmed')
                                                    <flux:button size="xs" variant="ghost" wire:click="updateBookingStatus({{ $booking->id }}, 'completed')">{{ __('Complete') }}</flux:button>
                                                @endif
                                                <flux:button size="xs" variant="danger" wire:click="updateBookingStatus({{ $booking->id }}, 'cancelled')">{{ __('Cancel') }}</flux:button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-neutral-400">{{ __('No reservations scheduled.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
