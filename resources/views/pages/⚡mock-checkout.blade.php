<?php

use Livewire\Component;
use App\Models\Booking;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Stripe Checkout'), Layout('layouts.auth.card')] class extends Component {
    public int $bookingId;
    public Booking $booking;
    public string $cardNumber = '4242 4242 4242 4242';
    public string $expiry = '12/29';
    public string $cvc = '123';
    public string $nameOnCard = '';

    public function mount(int $id): void
    {
        $this->bookingId = $id;
        $this->booking = Booking::findOrFail($id);
        $this->nameOnCard = auth()->user()->name;
    }

    public function processMockPayment(): void
    {
        $this->validate([
            'cardNumber' => 'required',
            'expiry' => 'required',
            'cvc' => 'required',
            'nameOnCard' => 'required|string',
        ]);

        Flux::toast(variant: 'success', text: __('Processing payment...'));

        // Delay for 1 second to simulate network request
        $this->redirect(route('booking.success', ['id' => $this->bookingId]) . '?session_id=mock_stripe_' . Str::random(12));
    }
}; ?>

<div>
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800 pb-4">
            <div class="flex items-center gap-2">
                <span class="bg-indigo-600 text-white font-bold p-1 rounded-sm text-xs tracking-wider">STRIPE</span>
                <span class="text-sm font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Test Mode') }}</span>
            </div>
            <span class="text-sm text-neutral-400">{{ __('Booking ID:') }} #{{ $booking->id }}</span>
        </div>

        <div class="bg-neutral-50 dark:bg-stone-900 rounded-lg p-4 space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-neutral-500 dark:text-neutral-400">{{ __('Restaurant') }}</span>
                <span class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $booking->restaurant->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-neutral-500 dark:text-neutral-400">{{ __('Reservation') }}</span>
                <span class="text-neutral-800 dark:text-neutral-100">{{ $booking->booking_date->format('M d, Y') }} @ {{ \Carbon\Carbon::parse($booking->booking_time)->format('h:i A') }}</span>
            </div>
            <div class="flex justify-between border-t border-neutral-100 dark:border-neutral-800 pt-2 mt-2 font-semibold">
                <span class="text-neutral-800 dark:text-neutral-100">{{ __('Total Due') }}</span>
                <span class="text-indigo-600 dark:text-indigo-400 text-lg">₹{{ number_format($booking->payment_amount, 2) }}</span>
            </div>
        </div>

        <form wire:submit="processMockPayment" class="flex flex-col gap-4">
            <flux:input
                wire:model="nameOnCard"
                name="nameOnCard"
                :label="__('Cardholder Name')"
                type="text"
                required
                placeholder="John Doe"
            />

            <flux:input
                wire:model="cardNumber"
                name="cardNumber"
                :label="__('Card Number')"
                type="text"
                required
                icon="credit-card"
                placeholder="4242 4242 4242 4242"
            />

            <div class="grid grid-cols-2 gap-4">
                <flux:input
                    wire:model="expiry"
                    name="expiry"
                    :label="__('Expiration Date')"
                    type="text"
                    required
                    placeholder="MM/YY"
                />

                <flux:input
                    wire:model="cvc"
                    name="cvc"
                    :label="__('CVC')"
                    type="text"
                    required
                    placeholder="123"
                    maxlength="3"
                />
            </div>

            <flux:button variant="primary" type="submit" class="w-full mt-4 bg-indigo-600 hover:bg-indigo-700">
                {{ __('Pay ₹' . number_format($booking->payment_amount, 2)) }}
            </flux:button>
        </form>
    </div>
</div>
