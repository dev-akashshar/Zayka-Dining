<?php

use Livewire\Component;
use App\Models\ManagerRequest;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Stripe Checkout'), Layout('layouts.auth.card')] class extends Component {
    public int $requestId;
    public ManagerRequest $managerRequest;
    public string $cardNumber = '4242 4242 4242 4242';
    public string $expiry = '12/29';
    public string $cvc = '123';
    public string $nameOnCard = '';

    public function mount(int $id): void
    {
        $this->requestId = $id;
        $this->managerRequest = ManagerRequest::findOrFail($id);
        $this->nameOnCard = $this->managerRequest->name;
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

        $this->redirect(route('manager-request.success', ['id' => $this->requestId]) . '?session_id=mock_stripe_' . Str::random(12));
    }
}; ?>

<div>
    <div class="flex flex-col gap-6">
            <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800 pb-4">
                <div class="flex items-center gap-2">
                    <span class="bg-amber-600 text-white font-bold p-1 rounded-sm text-xs tracking-wider">STRIPE</span>
                    <span class="text-sm font-semibold text-neutral-500 dark:text-neutral-400">{{ __('Test Mode') }}</span>
                </div>
                <span class="text-sm text-neutral-400">{{ __('Request ID:') }} #{{ $managerRequest->id }}</span>
            </div>

            <div class="bg-neutral-50 dark:bg-stone-900 rounded-lg p-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-neutral-500 dark:text-neutral-400">{{ __('Restaurant') }}</span>
                    <span class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $managerRequest->restaurant_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-500 dark:text-neutral-400">{{ __('Manager') }}</span>
                    <span class="text-neutral-800 dark:text-neutral-100">{{ $managerRequest->name }} ({{ $managerRequest->email }})</span>
                </div>
                <div class="flex justify-between border-t border-neutral-100 dark:border-neutral-800 pt-2 mt-2 font-semibold">
                    <span class="text-neutral-800 dark:text-neutral-100">{{ __('Total Due') }}</span>
                    <span class="text-amber-600 dark:text-amber-400 text-lg">₹{{ number_format($managerRequest->payment_amount, 2) }}</span>
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

                <flux:button variant="primary" type="submit" class="w-full mt-4 bg-amber-600 hover:bg-amber-700">
                    {{ __('Pay ₹' . number_format($managerRequest->payment_amount, 2)) }}
                </flux:button>
            </form>
        </div>
</div>
