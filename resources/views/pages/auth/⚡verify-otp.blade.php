<?php

use Livewire\Component;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Flux\Flux;

new #[Title('Verify OTP'), Layout('layouts.auth.card')] class extends Component {
    public string $email = '';
    public string $code = '';

    public function mount(): void
    {
        $this->email = session('otp_email', '');

        if (empty($this->email)) {
            $this->redirect(route('login'), navigate: true);
        }
    }

    public function verify(): void
    {
        $this->validate([
            'code' => 'required|string|size:6',
        ]);

        if (Otp::verifyCode($this->email, $this->code)) {
            $user = User::where('email', $this->email)->first();

            if ($user) {
                // If user exists, log them in and redirect to dashboard
                Auth::login($user, remember: true);

                if (! $user->hasVerifiedEmail()) {
                    $user->markEmailAsVerified();
                }

                Flux::toast(variant: 'success', text: __('Logged in successfully.'));
                $this->redirect(route('dashboard'), navigate: true);
            } else {
                // If user does not exist, store verified email in session and redirect to register
                session(['register_email' => $this->email]);
                Flux::toast(variant: 'success', text: __('Email verified. Please complete your registration.'));
                $this->redirect(route('register'), navigate: true);
            }
        } else {
            $this->addError('code', __('Invalid or expired OTP code.'));
        }
    }

    public function resend(): void
    {
        if (empty($this->email)) {
            return;
        }

        try {
            $otp = Otp::generateForEmail($this->email);
            \Illuminate\Support\Facades\Mail::to($this->email)->send(new \App\Mail\OtpMail($otp->code));
            Flux::toast(variant: 'success', text: __('A new OTP code has been sent.'));
        } catch (\Exception $e) {
            logger()->error('Failed to resend OTP: ' . $e->getMessage());
            Flux::toast(variant: 'danger', text: __('Unable to resend OTP.'));
        }
    }
}; ?>

<div class="flex flex-col gap-6 w-full">
    <x-auth-header :title="__('Verify OTP')" :description="__('Enter the 6-digit verification code sent to ' . $email)" />

    <form wire:submit="verify" class="flex flex-col gap-6">
        <flux:input
            wire:model="code"
            name="code"
            :label="__('Verification Code')"
            type="text"
            required
            autofocus
            placeholder="123456"
            maxlength="6"
            class="text-center tracking-widest text-lg font-bold"
        />

        <div class="flex items-center justify-between">
            <flux:link wire:click="resend" class="text-sm cursor-pointer">
                {{ __('Resend Code') }}
            </flux:link>

            <flux:button variant="primary" type="submit">
                {{ __('Verify & Login') }}
            </flux:button>
        </div>
    </form>
</div>
