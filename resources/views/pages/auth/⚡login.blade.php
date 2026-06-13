<?php

use Livewire\Component;
use App\Models\Otp;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Flux\Flux;

new #[Title('Log in'), Layout('layouts.auth.card')] class extends Component {
    public string $email = '';

    public function mount(): void
    {
        if ($plan = request()->query('plan')) {
            session(['selected_plan' => $plan]);
        }
    }

    public function sendOtp(): void
    {
        $this->validate([
            'email' => 'required|email',
        ]);

        try {
            $otp = Otp::generateForEmail($this->email);

            Mail::to($this->email)->send(new OtpMail($otp->code));

            session(['otp_email' => $this->email]);

            Flux::toast(variant: 'success', text: __('OTP code sent to your email.'));

            $this->redirect(route('login.verify'), navigate: true);
        } catch (\Exception $e) {
            logger()->error('Failed to generate or send OTP: ' . $e->getMessage());
            $this->addError('email', __('Unable to send OTP. Please check your email and try again.'));
        }
    }
}; ?>

<div class="flex flex-col gap-6 w-full">
    <x-auth-header :title="__('Log in to Zayka Dining')" :description="__('Enter your email below to receive a 6-digit verification code')" />

    <form wire:submit="sendOtp" class="flex flex-col gap-6">
        <flux:input
            wire:model="email"
            name="email"
            :label="__('Email address')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="you@example.com"
        />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Send OTP Code') }}
            </flux:button>
        </div>
    </form>
</div>
