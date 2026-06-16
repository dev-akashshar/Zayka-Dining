<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            @php
                $user = auth()->user();
                $isSuperAdmin = $user?->hasRole('super_admin');
                $isManager = $user?->hasRole('manager');
                $isStaff = $user?->hasRole('staff');
                $isCustomer = !$isSuperAdmin && !$isManager && !$isStaff;

                if ($isSuperAdmin) {
                    $pendingRequestsCount = \App\Models\ManagerRequest::where('payment_status', 'paid')
                        ->where('status', 'pending_approval')
                        ->count();
                }
            @endphp

            <flux:sidebar.nav>
                @if ($isSuperAdmin)
                    <flux:sidebar.group :heading="__('SaaS Admin')" class="grid">
                        <flux:sidebar.item icon="home" :href="route('dashboard', ['tab' => 'dashboard'])" :current="request()->routeIs('dashboard') && (request()->query('tab', 'dashboard') === 'dashboard' || !request()->has('tab'))" class="sidebar-item-premium" wire:navigate>
                            {{ __('Admin Dashboard') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="building-storefront" :href="route('dashboard', ['tab' => 'restaurants'])" :current="request()->routeIs('dashboard') && request()->query('tab') === 'restaurants'" class="sidebar-item-premium" wire:navigate>
                            {{ __('Establishments') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('dashboard', ['tab' => 'users'])" :current="request()->routeIs('dashboard') && request()->query('tab') === 'users'" class="sidebar-item-premium" wire:navigate>
                            {{ __('User Accounts') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="shield-check" :href="route('dashboard', ['tab' => 'roles'])" :current="request()->routeIs('dashboard') && request()->query('tab') === 'roles'" class="sidebar-item-premium" wire:navigate>
                            {{ __('Role Auditor') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="envelope" :href="route('dashboard', ['tab' => 'requests'])" :current="request()->routeIs('dashboard') && request()->query('tab') === 'requests'" class="sidebar-item-premium" wire:navigate>
                            <span class="flex items-center justify-between w-full">
                                <span>{{ __('Manager Requests') }}</span>
                                @if ($pendingRequestsCount > 0)
                                    <span class="bg-red-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-sm">{{ $pendingRequestsCount }}</span>
                                @endif
                            </span>
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="route('dashboard', ['tab' => 'audits'])" :current="request()->routeIs('dashboard') && request()->query('tab') === 'audits'" class="sidebar-item-premium" wire:navigate>
                            {{ __('Audit Logs') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="command-line" :href="route('dashboard', ['tab' => 'logs'])" :current="request()->routeIs('dashboard') && request()->query('tab') === 'logs'" class="sidebar-item-premium" wire:navigate>
                            {{ __('Live Logs') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:separator class="my-3 opacity-60" />

                    <flux:sidebar.group :heading="__('System Utils')" class="grid">
                        <flux:sidebar.item icon="globe-alt" :href="route('home')" target="_blank" class="sidebar-item-premium">
                            {{ __('Public Storefront') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @elseif ($isManager)
                    <flux:sidebar.group :heading="__('Bistro Console')" class="grid">
                        <flux:sidebar.item icon="home" :href="route('dashboard', ['tab' => 'dashboard'])" :current="request()->routeIs('dashboard') && (request()->query('tab', 'dashboard') === 'dashboard' || !request()->has('tab'))" class="sidebar-item-premium" wire:navigate>
                            {{ __('Bistro Dashboard') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="route('dashboard', ['tab' => 'reservations'])" :current="request()->routeIs('dashboard') && request()->query('tab') === 'reservations'" class="sidebar-item-premium" wire:navigate>
                            {{ __('Reservations') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="list-bullet" :href="route('dashboard', ['tab' => 'menu'])" :current="request()->routeIs('dashboard') && request()->query('tab') === 'menu'" class="sidebar-item-premium" wire:navigate>
                            {{ __('Royal Menu') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="pencil-square" :href="route('dashboard', ['tab' => 'blogs'])" :current="request()->routeIs('dashboard') && request()->query('tab') === 'blogs'" class="sidebar-item-premium" wire:navigate>
                            {{ __('Culinary Blogs') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="table-cells" :href="route('dashboard', ['tab' => 'tables'])" :current="request()->routeIs('dashboard') && request()->query('tab') === 'tables'" class="sidebar-item-premium" wire:navigate>
                            {{ __('Floor Tables') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('dashboard', ['tab' => 'staff'])" :current="request()->routeIs('dashboard') && request()->query('tab') === 'staff'" class="sidebar-item-premium" wire:navigate>
                            {{ __('Staff Roster') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="building-storefront" :href="route('dashboard', ['tab' => 'profile'])" :current="request()->routeIs('dashboard') && request()->query('tab') === 'profile'" class="sidebar-item-premium" wire:navigate>
                            {{ __('Bistro Details') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="command-line" :href="route('dashboard', ['tab' => 'logs'])" :current="request()->routeIs('dashboard') && request()->query('tab') === 'logs'" class="sidebar-item-premium" wire:navigate>
                            {{ __('Live Logs') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @elseif ($isStaff)
                    <flux:sidebar.group :heading="__('Staff Workspace')" class="grid">
                        <flux:sidebar.item icon="home" :href="route('dashboard', ['tab' => 'dashboard'])" :current="request()->routeIs('dashboard') && (request()->query('tab', 'dashboard') === 'dashboard' || !request()->has('tab'))" class="sidebar-item-premium" wire:navigate>
                            {{ __('Staff Dashboard') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clock" :href="route('dashboard', ['tab' => 'shifts'])" :current="request()->routeIs('dashboard') && request()->query('tab') === 'shifts'" class="sidebar-item-premium" wire:navigate>
                            {{ __('Shift Tracker') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="route('dashboard', ['tab' => 'bookings'])" :current="request()->routeIs('dashboard') && request()->query('tab') === 'bookings'" class="sidebar-item-premium" wire:navigate>
                            {{ __('Floor Bookings') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="table-cells" :href="route('dashboard', ['tab' => 'tables'])" :current="request()->routeIs('dashboard') && request()->query('tab') === 'tables'" class="sidebar-item-premium" wire:navigate>
                            {{ __('Floor Tables') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="list-bullet" :href="route('dashboard', ['tab' => 'menu'])" :current="request()->routeIs('dashboard') && request()->query('tab') === 'menu'" class="sidebar-item-premium" wire:navigate>
                            {{ __('Bistro Menu') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @else
                    <flux:sidebar.group :heading="__('Table Booking')" class="grid">
                        <flux:sidebar.item icon="home" :href="route('dashboard', ['tab' => 'dashboard'])" :current="request()->routeIs('dashboard') && (request()->query('tab', 'dashboard') === 'dashboard' || !request()->has('tab'))" class="sidebar-item-premium" wire:navigate>
                            {{ __('Diner Dashboard') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="plus-circle" :href="route('dashboard', ['tab' => 'reserve'])" :current="request()->routeIs('dashboard') && request()->query('tab') === 'reserve'" class="sidebar-item-premium" wire:navigate>
                            {{ __('Reserve a Table') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="route('dashboard', ['tab' => 'history'])" :current="request()->routeIs('dashboard') && request()->query('tab') === 'history'" class="sidebar-item-premium" wire:navigate>
                            {{ __('Booking History') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    :src="auth()->user()->profile_photo_path ? asset('storage/' . auth()->user()->profile_photo_path) : null"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                    :src="auth()->user()->profile_photo_path ? asset('storage/' . auth()->user()->profile_photo_path) : null"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
