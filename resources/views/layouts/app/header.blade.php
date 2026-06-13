<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

            @php
                $user = auth()->user();
                $isSuperAdmin = $user?->hasRole('super_admin');
                $isManager = $user?->hasRole('manager');
                $isStaff = $user?->hasRole('staff');
                $isCustomer = !$isSuperAdmin && !$isManager && !$isStaff;
            @endphp

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Workspace Dashboard') }}
                </flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
                <flux:tooltip :content="__('Search')" position="bottom">
                    <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="magnifying-glass" href="#" :label="__('Search')" />
                </flux:tooltip>
            </flux:navbar>

            <x-desktop-user-menu />
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                @if ($isSuperAdmin)
                    <flux:sidebar.group :heading="__('SaaS Admin')">
                        <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard') && !str_contains(request()->fullUrl(), '#')" wire:navigate>
                            {{ __('Admin Dashboard') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="building-storefront" :href="route('dashboard') . '#restaurants-management'">
                            {{ __('Establishments') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('dashboard') . '#users-management'">
                            {{ __('User Accounts') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="shield-check" :href="route('dashboard') . '#roles-management'">
                            {{ __('Role Auditor') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="route('dashboard') . '#audit-logs'">
                            {{ __('Audit Logs') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @elseif ($isManager)
                    <flux:sidebar.group :heading="__('Bistro Console')">
                        <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard') && !str_contains(request()->fullUrl(), '#')" wire:navigate>
                            {{ __('Bistro Dashboard') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="route('dashboard') . '#reservations-console'">
                            {{ __('Reservations') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="list-bullet" :href="route('dashboard') . '#menu-management'">
                            {{ __('Royal Menu') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="pencil-square" :href="route('dashboard') . '#blog-management'">
                            {{ __('Culinary Blogs') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="table-cells" :href="route('dashboard') . '#seating-tables'">
                            {{ __('Floor Tables') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('dashboard') . '#staff-roster'">
                            {{ __('Staff Roster') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clock" :href="route('dashboard') . '#attendance-logs'">
                            {{ __('Attendance Logs') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @elseif ($isStaff)
                    <flux:sidebar.group :heading="__('Staff Workspace')">
                        <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                            {{ __('Staff Dashboard') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clock" :href="route('dashboard') . '#shift-console'">
                            {{ __('Shift Roster') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="route('dashboard') . '#assigned-bookings'">
                            {{ __('Assigned Bookings') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @else
                    <flux:sidebar.group :heading="__('Table Booking')">
                        <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                            {{ __('Diner Dashboard') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="plus-circle" :href="route('dashboard') . '#reserve-table'">
                            {{ __('Reserve a Table') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="route('dashboard') . '#booking-history'">
                            {{ __('Booking History') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />
        </flux:sidebar>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
