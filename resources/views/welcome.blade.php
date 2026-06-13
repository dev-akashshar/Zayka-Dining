<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zayka Dining - Premium Indian Multi-Restaurant Reservation Workspace</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind Asset -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js CDN for interactive UI -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        h1, h2, h3, h4, .font-display {
            font-family: 'Outfit', sans-serif;
        }
        .font-serif-indian {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>
<body class="bg-stone-50 text-stone-900 dark:bg-stone-950 dark:text-stone-50 min-h-screen selection:bg-amber-600 selection:text-white transition-colors duration-300">

    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-stone-50/80 dark:bg-stone-950/80 border-b border-amber-900/10 dark:border-amber-100/10 transition-colors">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3 group">
                <div class="bg-amber-600 text-white rounded-xl p-2.5 flex items-center justify-center shadow-lg shadow-amber-600/30 group-hover:scale-105 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v4M12 6a6 6 0 0 0-6 6h12a6 6 0 0 0-6-6ZM3 14h18M5 14v2a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3v-2M12 19v3M9 22h6" />
                    </svg>
                </div>
                <span class="text-2xl font-bold tracking-tight bg-gradient-to-r from-amber-800 to-orange-600 dark:from-amber-200 dark:to-orange-400 bg-clip-text text-transparent">Zayka <span class="font-serif-indian italic font-medium">Dining</span></span>
            </a>

            <!-- Nav Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-stone-600 dark:text-stone-300">
                <a href="#restaurants" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Restaurants</a>
                <a href="{{ route('blogs') }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Blogs</a>
                <a href="{{ route('about') }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors">About Us</a>
                <a href="{{ route('contact') }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Contact</a>
            </nav>

            <!-- Action buttons -->
            <div class="flex items-center gap-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-5 py-2.5 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-xl transition-all shadow-md shadow-amber-600/20 hover:shadow-amber-600/30 flex items-center gap-2">
                            <span>Go to Dashboard</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2.5 text-sm font-semibold hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                            Log in
                        </a>
                        <a href="{{ route('register') }}" class="px-5 py-2.5 text-sm font-semibold text-white bg-stone-900 hover:bg-stone-800 dark:bg-amber-600 dark:hover:bg-amber-700 rounded-xl transition-all shadow-md">
                            Get Started
                        </a>
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative overflow-hidden pt-16 pb-20 lg:pt-24 lg:pb-28">
        <!-- Indian Aesthetic Background motifs -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-amber-500/10 rounded-full blur-3xl -z-10 pointer-events-none"></div>
        <div class="absolute top-1/3 left-1/4 w-[350px] h-[350px] bg-orange-500/10 rounded-full blur-3xl -z-10 pointer-events-none"></div>

        @if (session('status') === 'manager-request-submitted')
            <div class="max-w-7xl mx-auto px-6 mb-6">
                <div class="bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-800/40 rounded-xl p-4 text-green-800 dark:text-green-400 text-sm flex items-center gap-2">
                    <svg class="size-5 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ __('Payment successful! Your restaurant registration request has been submitted to the Admin. Once approved, your manager account will be provisioned.') }}</span>
                </div>
            </div>
        @endif

        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Left Info -->
            <div class="lg:col-span-7 space-y-8 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 bg-amber-500/10 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 px-4 py-2 rounded-full text-xs font-semibold uppercase tracking-wider border border-amber-500/20">
                    <i class="fa-solid fa-hotel text-amber-600"></i> Authentic Indian Hospitality & Modern Dining
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-none text-stone-900 dark:text-stone-50">
                    Experience Royal <br>
                    <span class="font-serif-indian italic text-amber-600 font-semibold">Indian Hospitality</span> & Reservations
                </h1>
                <p class="text-lg text-stone-600 dark:text-stone-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                    Welcome to the ultimate reservation & staff workspace tailored for Indian restaurants, fine-dining bistros, and banquet halls. Seamlessly manage tables, staff rosters, attendance logs, and instant Stripe pre-payments.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="{{ route('register') }}" class="px-8 py-4 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-semibold transition-all shadow-lg shadow-amber-600/30 hover:scale-[1.02] flex items-center justify-center gap-2">
                        <span>Provision Your Restaurant</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                    <a href="#features" class="px-8 py-4 bg-white dark:bg-stone-900 hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-800 dark:text-stone-200 border border-stone-200 dark:border-stone-800 rounded-xl font-semibold transition-all flex items-center justify-center gap-2">
                        Explore Portals
                    </a>
                </div>

                <!-- Indian theme food-inspired statistics -->
                <div class="grid grid-cols-3 gap-6 pt-6 border-t border-stone-200 dark:border-stone-800 max-w-lg mx-auto lg:mx-0">
                    <div>
                        <div class="text-3xl font-extrabold text-amber-600 dark:text-amber-400">250+</div>
                        <div class="text-xs text-stone-500 dark:text-stone-400">Indian Bistros</div>
                    </div>
                    <div>
                        <div class="text-3xl font-extrabold text-amber-600 dark:text-amber-400">55k+</div>
                        <div class="text-xs text-stone-500 dark:text-stone-400">Gourmet Bookings</div>
                    </div>
                    <div>
                        <div class="text-3xl font-extrabold text-orange-500 dark:text-orange-400">₹3.5Cr+</div>
                        <div class="text-xs text-stone-500 dark:text-stone-400">Processed Securely</div>
                    </div>
                </div>
            </div>

            <!-- Right Graphic -->
            <div class="lg:col-span-5 relative mt-6 lg:mt-0">
                <div class="relative mx-auto max-w-[420px] bg-white dark:bg-stone-900 rounded-3xl border border-amber-900/10 dark:border-amber-100/10 shadow-2xl p-6 overflow-hidden group hover:shadow-3xl transition-shadow duration-300">
                    <div class="flex items-center justify-between border-b border-stone-100 dark:border-stone-800 pb-3 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="size-2.5 rounded-full bg-orange-500"></span>
                            <span class="size-2.5 rounded-full bg-yellow-400"></span>
                            <span class="size-2.5 rounded-full bg-green-500"></span>
                        </div>
                        <span class="text-xs font-semibold text-amber-700 dark:text-amber-400 tracking-wider font-mono">ZAYKA CENTRAL CONSOLE</span>
                    </div>

                    <!-- Inner Mockup Indian Menu / Roster UI -->
                    <div class="space-y-4">
                        <div class="bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl p-4 flex items-center justify-between shadow-md">
                            <div>
                                <div class="text-xs uppercase font-semibold text-amber-100">Today's Restaurant Billings</div>
                                <div class="text-2xl font-extrabold mt-1">₹45,850.00</div>
                            </div>
                            <i class="fa-solid fa-bowl-food text-3xl opacity-90"></i>
                        </div>

                        <!-- Reservation Feed -->
                        <div class="space-y-2.5">
                            <div class="text-xs font-bold text-stone-400 uppercase tracking-wider">Active Bookings - Royal Hall</div>
                            <div class="border border-stone-100 dark:border-stone-800 rounded-xl p-3 flex justify-between items-center bg-stone-50/50 dark:bg-stone-900/50">
                                <div>
                                    <div class="text-sm font-bold">Rohan Sharma</div>
                                    <div class="text-xs text-stone-500">Table 4 (Maharaja Seat) • 4 Guests</div>
                                </div>
                                <span class="bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 text-xs px-2.5 py-1 rounded-full font-bold">Pending Confirmation</span>
                            </div>
                            <div class="border border-stone-100 dark:border-stone-800 rounded-xl p-3 flex justify-between items-center bg-stone-50/50 dark:bg-stone-900/50">
                                <div>
                                    <div class="text-sm font-bold">Priya Patel</div>
                                    <div class="text-xs text-stone-500">Table 12 (Spiced Terrace) • 2 Guests</div>
                                </div>
                                <span class="bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 text-xs px-2.5 py-1 rounded-full font-bold">Paid Advance</span>
                            </div>
                        </div>

                        <!-- Indian hospitality chef status -->
                        <div class="bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40 rounded-xl p-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="size-2 bg-orange-500 rounded-full animate-ping"></span>
                                <span class="text-xs font-semibold text-amber-900 dark:text-amber-400">Head Chef Devendra (Kitchen A) is ON SHIFT</span>
                            </div>
                            <span class="text-xs font-medium text-amber-700 dark:text-amber-500">Checked-in 4h ago</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Royal Restaurants Section -->
<section id="restaurants" class="py-20 border-t border-slate-200/80 dark:border-zinc-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 space-y-12">
            <div class="text-center max-w-2xl mx-auto space-y-3">
                <div class="inline-flex items-center gap-1.5 bg-zinc-900/5 dark:bg-zinc-100/5 text-zinc-800 dark:text-zinc-300 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border border-zinc-200 dark:border-zinc-800">
                    <i class="fa-solid fa-map-location-dot text-amber-600"></i> {{ __('Curated Culinary Destinations') }}
                </div>
                <h2 class="text-3xl sm:text-4xl font-black tracking-tight">Our Registered Outlets</h2>
                <p class="text-slate-500 dark:text-zinc-400 text-sm sm:text-base">
                    Discover authentic regional flavors, premium dining environments, and master chefs operating within your parameters.
                </p>
            </div>

            @php
                $restaurants = \App\Models\Restaurant::where('is_active', true)->get();
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                @forelse ($restaurants as $resto)
                    <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800/80 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between group">
                        <div>
                            <div class="relative h-48 bg-slate-100 dark:bg-zinc-950 overflow-hidden">
                                @if ($resto->image_path)
                                    <img src="{{ asset('storage/' . $resto->image_path) }}" alt="{{ $resto->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-zinc-900 to-zinc-800 dark:from-zinc-900 dark:to-zinc-950 flex flex-col items-center justify-center text-white gap-2">
                                        <i class="fa-solid fa-bowl-food text-3xl text-amber-500"></i>
                                        <span class="font-serif-indian italic text-xs font-semibold tracking-wider text-zinc-300">Zayka Dining Framework</span>
                                    </div>
                                @endif
                                <div class="absolute top-4 right-4 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md px-2.5 py-1 rounded-lg text-xs font-bold text-amber-600 flex items-center gap-1 shadow-sm border border-black/5">
                                    <i class="fa-solid fa-star text-[10px]"></i>
                                    <span>{{ number_format($resto->rating, 1) }}</span>
                                </div>
                            </div>

                            <div class="p-6 space-y-3.5">
                                <h3 class="text-lg font-bold text-slate-950 dark:text-zinc-50 tracking-tight">{{ $resto->name }}</h3>
                                <div class="space-y-1.5">
                                    <p class="text-xs text-slate-500 dark:text-zinc-400 flex items-start gap-2">
                                        <i class="fa-solid fa-location-dot text-amber-600 shrink-0 mt-0.5"></i>
                                        <span class="line-clamp-1">{{ $resto->address }}</span>
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-zinc-400 flex items-center gap-2">
                                        <i class="fa-solid fa-phone text-amber-600 shrink-0"></i>
                                        <span>{{ $resto->phone ?: __('+91 Not Provided') }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 pb-6 pt-1">
                            <a href="{{ route('restaurant.landing', ['id' => $resto->id]) }}" class="w-full text-center py-3 bg-zinc-950 hover:bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-950 dark:hover:bg-zinc-200 text-xs font-bold rounded-xl block transition-all shadow-sm">
                                View Layout & Book
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-16 text-zinc-400 bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800/80 rounded-2xl">
                        <i class="fa-solid fa-utensils text-3xl mb-3 text-zinc-300 dark:text-zinc-700"></i>
                        <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">{{ __('No operational bistros registered in database architecture.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Interactive Role Portals Section -->
    <section id="features" class="py-20 bg-white dark:bg-stone-900 border-y border-stone-200 dark:border-stone-800 transition-colors" x-data="{ tab: 'booking' }">
        <div class="max-w-7xl mx-auto px-6 space-y-12">
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Four Distinct Portals, One Seamless System</h2>
                <p class="text-stone-600 dark:text-stone-400 text-lg">
                    Zayka Dining integrates customers, staff members, restaurant managers, and system administrators under a single unified environment.
                </p>
            </div>

            <!-- Tab Buttons -->
            <div class="flex flex-wrap justify-center gap-4 border-b border-stone-200 dark:border-stone-800 pb-4">
                <button @click="tab = 'booking'" :class="tab === 'booking' ? 'bg-amber-600 text-white shadow-md' : 'hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-600 dark:text-stone-300'" class="px-5 py-3 rounded-xl font-semibold transition-all flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-utensils"></i> Customer Portal (Reservations)
                </button>
                <button @click="tab = 'staff'" :class="tab === 'staff' ? 'bg-amber-600 text-white shadow-md' : 'hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-600 dark:text-stone-300'" class="px-5 py-3 rounded-xl font-semibold transition-all flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-clock-rotate-left"></i> Staff Workspace (Shift Logs)
                </button>
                <button @click="tab = 'manager'" :class="tab === 'manager' ? 'bg-amber-600 text-white shadow-md' : 'hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-600 dark:text-stone-300'" class="px-5 py-3 rounded-xl font-semibold transition-all flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-chart-line"></i> Manager Console (Analytics)
                </button>
                <button @click="tab = 'admin'" :class="tab === 'admin' ? 'bg-amber-600 text-white shadow-md' : 'hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-600 dark:text-stone-300'" class="px-5 py-3 rounded-xl font-semibold transition-all flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-toolbox"></i> Super Admin Panel (SaaS Management)
                </button>
            </div>

            <!-- Tab Content -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center pt-8">
                <!-- Text Details -->
                <div class="space-y-6">
                    <div x-show="tab === 'booking'" class="space-y-6">
                        <h3 class="text-3xl font-extrabold text-stone-950 dark:text-stone-50">Reserve Royal Seats & Pre-Pay Instantly</h3>
                        <p class="text-stone-600 dark:text-stone-400 leading-relaxed">
                            Diners can look up regional restaurants, search for available tables live, and instantly secure seats with either an advance confirmation deposit or full pre-payment handled via Stripe.
                        </p>
                        <ul class="space-y-3 font-semibold text-stone-700 dark:text-stone-300">
                            <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-amber-600"></i> Prevent double-bookings with automated checks</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-amber-600"></i> Receive automated PDF invoices instantly to your email inbox</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-amber-600"></i> Seamless OTP-based registration and verification code flows</li>
                        </ul>
                    </div>
                    <div x-show="tab === 'staff'" class="space-y-6">
                        <h3 class="text-3xl font-extrabold text-stone-950 dark:text-stone-50">Chef & Waiter Roster Workspaces</h3>
                        <p class="text-stone-600 dark:text-stone-400 leading-relaxed">
                            Staff members can perform shift check-ins and check-outs, view active seating assignments, manage reservation statuses, and coordinate service logs efficiently.
                        </p>
                        <ul class="space-y-3 font-semibold text-stone-700 dark:text-stone-300">
                            <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-amber-600"></i> Single-click shift log updates with notes</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-amber-600"></i> Real-time display of guest arrivals and assigned dining tables</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-amber-600"></i> Integrated task checklists and notifications</li>
                        </ul>
                    </div>
                    <div x-show="tab === 'manager'" class="space-y-6">
                        <h3 class="text-3xl font-extrabold text-stone-950 dark:text-stone-50">Bistro Management, Floors, & Staff Roster</h3>
                        <p class="text-stone-600 dark:text-stone-400 leading-relaxed">
                            Establishment owners customize menu profiles, configure dining tables, track staff attendance histories, and accept or reject bookings instantly.
                        </p>
                        <ul class="space-y-3 font-semibold text-stone-700 dark:text-stone-300">
                            <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-amber-600"></i> Approve or reject incoming reservation requests with notes</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-amber-600"></i> Complete monthly billing audit history and sales reports</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-amber-600"></i> Track staff shifts, total worked durations, and active chefs</li>
                        </ul>
                    </div>
                    <div x-show="tab === 'admin'" class="space-y-6">
                        <h3 class="text-3xl font-extrabold text-stone-950 dark:text-stone-50">Global SaaS Control Board</h3>
                        <p class="text-stone-600 dark:text-stone-400 leading-relaxed">
                            Super Administrators maintain system oversight. Manage restaurant listings, review platform transactions, demote/promote user accounts, and update permissions.
                        </p>
                        <ul class="space-y-3 font-semibold text-stone-700 dark:text-stone-300">
                            <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-amber-600"></i> Toggle tenant statuses (active/locked/suspended)</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-amber-600"></i> Spatie Roles & Permissions auditor and control logs</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-amber-600"></i> Centralized metrics monitoring and database stats</li>
                        </ul>
                    </div>

                    <div class="pt-4">
                        <a href="{{ route('login') }}" class="px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-semibold inline-flex items-center gap-2 transition-all">
                            <span>Register / Login to Portal</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Graphic/Visual container -->
                <div class="bg-gradient-to-tr from-amber-600 to-orange-800 rounded-3xl p-8 text-white shadow-xl min-h-[320px] flex items-center justify-center relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-xl"></div>
                    <div class="absolute -left-10 -top-10 w-48 h-48 bg-amber-500/30 rounded-full blur-xl"></div>

                    <!-- Show changing preview depending on tab selection -->
                    <div class="space-y-4 w-full z-10">
                        <div class="bg-stone-900/30 backdrop-blur-md rounded-2xl p-6 border border-white/10">
                            <div class="flex items-center justify-between border-b border-white/15 pb-3 mb-4">
                                <span class="font-bold tracking-tight" x-text="tab === 'booking' ? 'Royal Maharaja Seating' : (tab === 'staff' ? 'Staff Shift Active' : (tab === 'manager' ? 'Spice Bistro Settings' : 'SaaS Master Admin'))"></span>
                                <span class="text-xs font-semibold px-2 py-0.5 bg-white/20 rounded-full uppercase" x-text="tab"></span>
                            </div>

                            <div x-show="tab === 'booking'" class="space-y-3">
                                <div class="flex justify-between items-center text-sm">
                                    <span>Advance Table Deposit</span>
                                    <span class="font-bold">₹1,250.00</span>
                                </div>
                                <div class="bg-green-600 text-xs font-bold text-center py-2 rounded-lg">✓ Stripe Payment Intent Successful</div>
                            </div>

                            <div x-show="tab === 'staff'" class="space-y-3">
                                <div class="text-center font-bold text-lg">Active Shift: Chef Devendra</div>
                                <div class="text-center text-xs text-white/70">Check-In: 09:30 AM • Duration: 4h 15m</div>
                                <div class="bg-orange-500/80 hover:bg-orange-600/80 text-xs text-center font-bold py-2 rounded-lg cursor-pointer">Submit Duty Check-Out</div>
                            </div>

                            <div x-show="tab === 'manager'" class="space-y-2">
                                <div class="text-xs text-white/70">Restaurant Floor Plan</div>
                                <div class="flex justify-between">
                                    <span class="text-xs">Tables Setup:</span>
                                    <span class="font-bold text-xs">12 Active Tables</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-xs">Kitchen Staff:</span>
                                    <span class="font-bold text-xs">5 Chefs Registered</span>
                                </div>
                            </div>

                            <div x-show="tab === 'admin'" class="space-y-2">
                                <div class="text-xs text-white/70">System Performance Metrics</div>
                                <div class="flex justify-between">
                                    <span class="text-xs">Active Tenants:</span>
                                    <span class="font-bold text-xs">32 Establishments</span>
                                </div>
                                <div class="flex justify-between border-t border-white/15 pt-2 mt-2">
                                    <span class="text-sm font-bold">Total Platform Volume:</span>
                                    <span class="font-bold text-sm">₹14,50,000.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20 bg-stone-50 dark:bg-stone-950 transition-colors">
        <div class="max-w-7xl mx-auto px-6 space-y-12">
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Flexible SaaS Pricing for Restaurants</h2>
                <p class="text-stone-600 dark:text-stone-400 text-lg">
                    Scale your dining reservation capacity as you expand. Register your establishments now.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <!-- Plan 1 -->
                <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-3xl p-8 space-y-6 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="text-stone-500 uppercase text-xs font-bold tracking-wider">Bistro Starter</div>
                        <div class="text-4xl font-black text-stone-900 dark:text-stone-50">₹1,999<span class="text-sm font-medium text-stone-500">/mo</span></div>
                        <p class="text-stone-600 dark:text-stone-400 text-sm">Perfect for local cafes and standalone curry houses needing simple reservation features.</p>
                        <hr class="border-stone-100 dark:border-stone-800">
                        <ul class="space-y-3 text-sm font-medium text-stone-700 dark:text-stone-300">
                            <li><i class="fa-solid fa-check text-amber-600 mr-2"></i> 1 Active Restaurant Profile</li>
                            <li><i class="fa-solid fa-check text-amber-600 mr-2"></i> Up to 8 Seating Tables</li>
                            <li><i class="fa-solid fa-check text-amber-600 mr-2"></i> Max 5 Staff Members</li>
                            <li class="text-stone-400"><i class="fa-solid fa-xmark mr-2"></i> Custom Invoice Branding</li>
                        </ul>
                    </div>
                    <a href="{{ route('login', ['plan' => '1999']) }}" class="w-full text-center py-3 bg-stone-100 hover:bg-stone-200 dark:bg-stone-800 dark:hover:bg-stone-700 text-stone-900 dark:text-stone-50 rounded-xl font-semibold transition-all">Select Plan</a>
                </div>

                <!-- Plan 2 (Featured) -->
                <div class="bg-white dark:bg-stone-900 border-2 border-amber-600 rounded-3xl p-8 space-y-6 flex flex-col justify-between relative shadow-xl">
                    <div class="absolute top-0 right-1/2 translate-x-1/2 -translate-y-1/2 bg-amber-600 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Most Popular</div>
                    <div class="space-y-4">
                        <div class="text-amber-600 uppercase text-xs font-bold tracking-wider">Imperial Suite</div>
                        <div class="text-4xl font-black text-stone-900 dark:text-stone-50">₹4,999<span class="text-sm font-medium text-stone-500">/mo</span></div>
                        <p class="text-stone-600 dark:text-stone-400 text-sm">Ideal for premium multi-hall dining brands, hotel diners, and banquets.</p>
                        <hr class="border-stone-100 dark:border-stone-800">
                        <ul class="space-y-3 text-sm font-medium text-stone-700 dark:text-stone-300">
                            <li><i class="fa-solid fa-check text-amber-600 mr-2"></i> 3 Active Restaurant Branches</li>
                            <li><i class="fa-solid fa-check text-amber-600 mr-2"></i> Unlimited Seating Tables</li>
                            <li><i class="fa-solid fa-check text-amber-600 mr-2"></i> Up to 25 Staff Members</li>
                            <li><i class="fa-solid fa-check text-amber-600 mr-2"></i> Automated PDF Invoice Emails</li>
                        </ul>
                    </div>
                    <a href="{{ route('login', ['plan' => '4999']) }}" class="w-full text-center py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-semibold transition-all shadow-md shadow-amber-600/30">Select Plan</a>
                </div>

                <!-- Plan 3 -->
                <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-3xl p-8 space-y-6 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="text-stone-500 uppercase text-xs font-bold tracking-wider">Maharaja Enterprise</div>
                        <div class="text-4xl font-black text-stone-900 dark:text-stone-50">₹9,999<span class="text-sm font-medium text-stone-500">/mo</span></div>
                        <p class="text-stone-600 dark:text-stone-400 text-sm">For nationwide restaurant franchises and luxury hotel groups requiring dedicated infrastructure.</p>
                        <hr class="border-stone-100 dark:border-stone-800">
                        <ul class="space-y-3 text-sm font-medium text-stone-700 dark:text-stone-300">
                            <li><i class="fa-solid fa-check text-amber-600 mr-2"></i> Unlimited Restaurant Branches</li>
                            <li><i class="fa-solid fa-check text-amber-600 mr-2"></i> Unlimited Seating Tables & Staff</li>
                            <li><i class="fa-solid fa-check text-amber-600 mr-2"></i> API Access & Export Audits</li>
                            <li><i class="fa-solid fa-check text-amber-600 mr-2"></i> Dedicated Account Support</li>
                        </ul>
                    </div>
                    <a href="{{ route('login', ['plan' => '9999']) }}" class="w-full text-center py-3 bg-stone-100 hover:bg-stone-200 dark:bg-stone-800 dark:hover:bg-stone-700 text-stone-900 dark:text-stone-50 rounded-xl font-semibold transition-all">Select Plan</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-20 bg-white dark:bg-stone-900 transition-colors">
        <div class="max-w-7xl mx-auto px-6 space-y-12">
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Endorsed by Top Indian Restaurateurs</h2>
                <p class="text-stone-600 dark:text-stone-400 text-lg">
                    See how Zayka Dining helps managers run busy kitchens and dining rooms without booking errors.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                <div class="bg-stone-50 dark:bg-stone-950 p-8 rounded-3xl border border-stone-200 dark:border-stone-800 space-y-4">
                    <p class="text-stone-600 dark:text-stone-300 italic font-medium leading-relaxed">
                        "The staff attendance tracking and automated email invoices are game-changers. No more table overlaps or manual billing sheets. Zayka has completely simplified my manager duties."
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-amber-600 text-white flex items-center justify-center font-bold">VS</div>
                        <div>
                            <div class="font-bold">Vikram Singh</div>
                            <div class="text-xs text-stone-500">General Manager, Masala Heritage</div>
                        </div>
                    </div>
                </div>

                <div class="bg-stone-50 dark:bg-stone-950 p-8 rounded-3xl border border-stone-200 dark:border-stone-800 space-y-4">
                    <p class="text-stone-600 dark:text-stone-300 italic font-medium leading-relaxed">
                        "Diners love the seamless payment option using Stripe. They pay their advance holding deposit, get an instant invoice on email, and our hostesses are notified on the staff check-in dashboard."
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-orange-500 text-white flex items-center justify-center font-bold">AK</div>
                        <div>
                            <div class="font-bold">Ananya Kapoor</div>
                            <div class="text-xs text-stone-500">Director, The Royal Tandoor</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Story Section -->
    <section id="about" class="py-20 bg-stone-50 dark:bg-stone-950 transition-colors">
        <div class="max-w-4xl mx-auto px-6 text-center space-y-8">
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Our Story</h2>
            <p class="text-stone-600 dark:text-stone-300 text-lg leading-relaxed">
                Zayka Dining was conceptualized to solve the complex coordination required in Indian fine-dining operations. We merge rich aesthetics with bulletproof reservation logic, live shift checklists, and secure Stripe billing. Let us host your digital floor plan.
            </p>
            <div class="flex justify-center gap-2 text-amber-600">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-stone-900 text-white py-12 border-t border-stone-800">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="bg-amber-600 text-white rounded-lg p-2 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v4M12 6a6 6 0 0 0-6 6h12a6 6 0 0 0-6-6ZM3 14h18M5 14v2a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3v-2M12 19v3M9 22h6" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-amber-400">Zayka Dining</span>
                </div>
                <p class="text-stone-400 text-sm leading-relaxed">
                    Royal Indian Multi-Restaurant Reservation SaaS Workspace. Real-time table assignments, automated invoices, and shift logs.
                </p>
            </div>

            <div>
                <h4 class="font-bold text-sm uppercase tracking-wider mb-4 text-stone-300">Quick Links</h4>
                <ul class="space-y-2 text-stone-400 text-sm">
                    <li><a href="#features" class="hover:text-amber-400 transition-colors">Portals & Features</a></li>
                    <li><a href="#pricing" class="hover:text-amber-400 transition-colors">Pricing</a></li>
                    <li><a href="#testimonials" class="hover:text-amber-400 transition-colors">Testimonials</a></li>
                    <li><a href="#about" class="hover:text-amber-400 transition-colors">Our Story</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-sm uppercase tracking-wider mb-4 text-stone-300">Product Info</h4>
                <ul class="space-y-2 text-stone-400 text-sm">
                    <li><a href="{{ route('login') }}" class="hover:text-amber-400 transition-colors">Portal Log-In</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-amber-400 transition-colors">Provision Bistro</a></li>
                    <li><a href="#" class="hover:text-amber-400 transition-colors">Security Audit</a></li>
                    <li><a href="#" class="hover:text-amber-400 transition-colors">Stripe Integration</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-sm uppercase tracking-wider mb-4 text-stone-300">Contact</h4>
                <ul class="space-y-2 text-stone-400 text-sm">
                    <li><i class="fa-solid fa-envelope mr-2 text-amber-500"></i> support@zaykadining.com</li>
                    <li><i class="fa-solid fa-phone mr-2 text-amber-500"></i> +91 11-45000000</li>
                    <li><i class="fa-solid fa-location-dot mr-2 text-amber-500"></i> New Delhi, India</li>
                </ul>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 pt-8 mt-8 border-t border-stone-800 text-center text-stone-500 text-xs flex flex-col sm:flex-row justify-between items-center gap-4">
            <p>&copy; {{ now()->year }} Zayka Dining. All rights reserved.</p>
            <div class="flex gap-4">
                <a href="#" class="hover:text-stone-300">Privacy Policy</a>
                <a href="#" class="hover:text-stone-300">Terms of Service</a>
            </div>
        </div>
    </footer>

</body>
</html>
