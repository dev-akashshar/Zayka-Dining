<!DOCTYPE html>
<html lang="en" class="scroll-smooth dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About Us - Zayka Dining</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind Asset -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
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
<body class="bg-[#0b0b0c] text-stone-100 min-h-screen selection:bg-amber-600 selection:text-white transition-colors duration-300">
 
    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-[#0b0b0c]/85 border-b border-white/5">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3 group">
                <div class="bg-amber-600 text-white rounded-xl p-2.5 flex items-center justify-center shadow-lg shadow-amber-600/30 group-hover:scale-105 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v4M12 6a6 6 0 0 0-6 6h12a6 6 0 0 0-6-6ZM3 14h18M5 14v2a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3v-2M12 19v3M9 22h6" />
                    </svg>
                </div>
                <span class="text-2xl font-black tracking-tight bg-gradient-to-r from-amber-200 to-orange-400 bg-clip-text text-transparent">Zayka <span class="font-serif-indian italic font-semibold">Dining</span></span>
            </a>
 
            <!-- Nav Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-stone-300">
                <a href="/" class="hover:text-amber-400 transition-colors">Home</a>
                <a href="{{ route('blogs') }}" class="hover:text-amber-400 transition-colors">Blogs</a>
                <a href="{{ route('about') }}" class="text-amber-400 font-bold">About Us</a>
                <a href="{{ route('contact') }}" class="hover:text-amber-400 transition-colors">Contact</a>
            </nav>
 
            <!-- Action buttons + Theme Toggle -->
            <div class="flex items-center gap-3">
                <button onclick="toggleZaykaTheme()" class="p-2.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-stone-300 hover:text-amber-400 transition-all" title="Toggle theme">
                    <i class="fa-solid fa-sun text-sm sun-icon hidden"></i>
                    <i class="fa-solid fa-moon text-sm moon-icon"></i>
                </button>
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 text-sm font-bold text-black bg-amber-500 hover:bg-amber-600 rounded-xl transition-all shadow-lg shadow-amber-500/20 flex items-center gap-2">
                        Dashboard <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2.5 text-sm font-bold text-stone-300 hover:text-white transition-colors">Log in</a>
                    <a href="{{ route('register') }}" class="px-5 py-2.5 text-sm font-bold text-white bg-zinc-800 hover:bg-zinc-700 rounded-xl transition-all shadow-md border border-white/5">Get Started</a>
                @endauth
            </div>
        </div>
    </header>
 
    <!-- Hero Band -->
    <section class="py-16 border-b border-white/5 text-center">
        <div class="max-w-4xl mx-auto px-6 space-y-4">
            <div class="inline-flex items-center gap-1.5 bg-amber-500/10 text-amber-400 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border border-amber-500/15 mb-4">
                <i class="fa-solid fa-hotel animate-pulse"></i> Our Story
            </div>
            <h1 class="text-4xl sm:text-5xl font-black text-stone-100 tracking-tight leading-tight">About Zayka Dining</h1>
            <p class="text-stone-400 text-lg leading-relaxed max-w-2xl mx-auto">
                Zayka Dining represents the culmination of authentic Indian hospitality and premium reservation management. We provide a state-of-the-art workspace for diners, waiters, chefs, managers, and system administrators.
            </p>
        </div>
    </section>

    <!-- Main content -->
    <main class="max-w-5xl mx-auto px-6 py-16 space-y-16">
        <!-- Feature Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-[#121214] border border-white/5 p-8 rounded-3xl space-y-4 hover:border-amber-500/20 transition-all duration-300 group">
                <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center">
                    <i class="fa-solid fa-utensils text-amber-500 text-lg"></i>
                </div>
                <h3 class="font-black text-lg text-stone-100">Multi-Restaurant Isolation</h3>
                <p class="text-sm text-stone-400 leading-relaxed">
                    Establishments operate on isolated datasets. Managers configure physical layout capacities, rosters, menus, and booking requests independently.
                </p>
            </div>
 
            <div class="bg-[#121214] border border-white/5 p-8 rounded-3xl space-y-4 hover:border-amber-500/20 transition-all duration-300 group">
                <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center">
                    <i class="fa-solid fa-credit-card text-amber-500 text-lg"></i>
                </div>
                <h3 class="font-black text-lg text-stone-100">Stripe Pre-Payments</h3>
                <p class="text-sm text-stone-400 leading-relaxed">
                    Prevent no-shows by collecting advance table reservation holdings or full seat prepayments directly through secure Stripe payment channels.
                </p>
            </div>
 
            <div class="bg-[#121214] border border-white/5 p-8 rounded-3xl space-y-4 hover:border-amber-500/20 transition-all duration-300 group">
                <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center">
                    <i class="fa-solid fa-clipboard-user text-amber-500 text-lg"></i>
                </div>
                <h3 class="font-black text-lg text-stone-100">Staff Duty Roster</h3>
                <p class="text-sm text-stone-400 leading-relaxed">
                    Chefs and hosting waiters execute digital check-in and check-out attendance duty rosters complete with status logs and managers auditing panels.
                </p>
            </div>
 
            <div class="bg-[#121214] border border-white/5 p-8 rounded-3xl space-y-4 hover:border-amber-500/20 transition-all duration-300 group">
                <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center">
                    <i class="fa-solid fa-file-invoice text-amber-500 text-lg"></i>
                </div>
                <h3 class="font-black text-lg text-stone-100">Automated PDF Invoices</h3>
                <p class="text-sm text-stone-400 leading-relaxed">
                    Instantly generate and distribute beautifully formatted PDF invoices directly to customer email inboxes upon processing reservation deposits.
                </p>
            </div>

            <div class="bg-[#121214] border border-white/5 p-8 rounded-3xl space-y-4 hover:border-amber-500/20 transition-all duration-300 group">
                <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center">
                    <i class="fa-solid fa-chart-line text-amber-500 text-lg"></i>
                </div>
                <h3 class="font-black text-lg text-stone-100">Live Analytics Dashboard</h3>
                <p class="text-sm text-stone-400 leading-relaxed">
                    Managers get real-time KPI metrics — revenue, bookings, staff shift durations, and table occupancy rates — all from a single sleek console.
                </p>
            </div>

            <div class="bg-[#121214] border border-white/5 p-8 rounded-3xl space-y-4 hover:border-amber-500/20 transition-all duration-300 group">
                <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center">
                    <i class="fa-solid fa-shield-halved text-amber-500 text-lg"></i>
                </div>
                <h3 class="font-black text-lg text-stone-100">Role-Based Access Control</h3>
                <p class="text-sm text-stone-400 leading-relaxed">
                    Powered by Spatie Roles & Permissions. Super admins, managers, staff, and customers each have precisely scoped access to protect sensitive data.
                </p>
            </div>
        </div>

        <!-- CTA Banner -->
        <div class="bg-gradient-to-r from-amber-600 to-orange-700 rounded-3xl p-10 text-center space-y-5 text-white" style="box-shadow: 0 0 80px -20px rgba(217,119,6,0.4);">
            <h2 class="text-3xl font-black">Ready to onboard your restaurant?</h2>
            <p class="text-amber-100 text-sm leading-relaxed max-w-xl mx-auto">Join hundreds of Indian fine dining establishments already using Zayka Dining to manage reservations and delight guests.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="px-8 py-4 bg-black/20 hover:bg-black/30 backdrop-blur-md border border-white/20 text-white font-extrabold rounded-xl transition-all">
                    Get Started Free
                </a>
                <a href="{{ route('contact') }}" class="px-8 py-4 bg-white text-amber-700 font-extrabold rounded-xl hover:bg-amber-50 transition-all">
                    Contact Sales
                </a>
            </div>
        </div>
    </main>
 
    <!-- Footer -->
    <footer class="bg-black text-stone-300 py-12 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-6 text-center text-stone-600 text-xs">
            <p>&copy; {{ now()->year }} Zayka Dining. All rights reserved.</p>
        </div>
    </footer>

    <script>
    function toggleZaykaTheme() {
        const html = document.documentElement;
        const isDark = html.classList.contains('dark');
        if (isDark) {
            html.classList.remove('dark');
            localStorage.setItem('zaykaTheme', 'light');
            document.body.style.backgroundColor = '#fafaf9';
            document.body.style.color = '#1c1917';
        } else {
            html.classList.add('dark');
            localStorage.setItem('zaykaTheme', 'dark');
            document.body.style.backgroundColor = '#0b0b0c';
            document.body.style.color = '#f5f5f4';
        }
        const sun = document.querySelector('.sun-icon');
        const moon = document.querySelector('.moon-icon');
        if (sun) sun.classList.toggle('hidden');
        if (moon) moon.classList.toggle('hidden');
    }
    (function() {
        const theme = localStorage.getItem('zaykaTheme');
        if (theme === 'light') {
            document.documentElement.classList.remove('dark');
            document.body.style.backgroundColor = '#fafaf9';
            document.body.style.color = '#1c1917';
            setTimeout(() => {
                const sun = document.querySelector('.sun-icon');
                const moon = document.querySelector('.moon-icon');
                if (sun) sun.classList.add('hidden');
                if (moon) moon.classList.remove('hidden');
            }, 50);
        } else {
            document.documentElement.classList.add('dark');
            setTimeout(() => {
                const sun = document.querySelector('.sun-icon');
                const moon = document.querySelector('.moon-icon');
                if (sun) sun.classList.remove('hidden');
                if (moon) moon.classList.add('hidden');
            }, 50);
        }
    })();
    </script>
</body>
</html>
