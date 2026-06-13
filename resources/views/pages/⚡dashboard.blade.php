<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;

new #[Title('Dashboard')] class extends Component {
    public string $role = 'customer';

    public function mount(): void
    {
        $user = Auth::user();

        if ($user->hasRole('super_admin')) {
            $this->role = 'super_admin';
        } elseif ($user->hasRole('manager')) {
            $this->role = 'manager';
        } elseif ($user->hasRole('staff')) {
            $this->role = 'staff';
        } else {
            $this->role = 'customer';
        }
    }
}; ?>

<div class="w-full h-full">
    @if ($role === 'super_admin')
        <livewire:pages::dashboard.super-admin />
    @elseif ($role === 'manager')
        <livewire:pages::dashboard.manager />
    @elseif ($role === 'staff')
        <livewire:pages::dashboard.staff />
    @else
        <livewire:pages::dashboard.customer />
    @endif
</div>
