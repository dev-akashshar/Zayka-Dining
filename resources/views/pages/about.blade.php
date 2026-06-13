<!DOCTYPE html>
<html lang="en">
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
<body class="bg-stone-50 text-stone-900 dark:bg-stone-950 dark:text-stone-50 min-h-screen selection:bg-amber-600 selection:text-white transition-colors duration-300">
 
    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-stone-50/80 dark:bg-stone-950/80 border-b border-amber-900/10 dark:border-amber-100/10">
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
                <a href="/" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Home</a>
                <a href="{{ route('blogs') }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Blogs</a>
                <a href="{{ route('about') }}" class="text-amber-600 dark:text-amber-400 font-semibold">About Us</a>
                <a href="{{ route('contact') }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Contact</a>
            </nav>
 
            <!-- Action buttons -->
            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-xl transition-all shadow-md">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2.5 text-sm font-semibold hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                        Log in
                    </a>
                    <a href="{{ route('register') }}" class="px-5 py-2.5 text-sm font-semibold text-white bg-stone-900 hover:bg-stone-800 dark:bg-amber-600 dark:hover:bg-amber-700 rounded-xl transition-all shadow-md">
                        Get Started
                    </a>
                @endauth
            </div>
        </div>
    </header>
 
    <!-- Main content -->
    <main class="max-w-4xl mx-auto px-6 py-12 space-y-8">
        <h1 class="text-4xl font-extrabold text-stone-900 dark:text-stone-50 text-center">About Zayka Dining</h1>
        
        <p class="text-lg text-stone-600 dark:text-stone-300 leading-relaxed text-center">
            Zayka Dining represents the culmination of authentic Indian hospitality and premium reservation management. We provide a state-of-the-art workspace for diners, waiters, chefs, managers, and system administrators to synchronize reservations without overlaps.
        </p>
 
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-8">
            <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 p-6 rounded-2xl space-y-3">
                <h3 class="font-bold text-lg text-amber-700 dark:text-amber-400"><i class="fa-solid fa-utensils mr-2"></i>Multi-Restaurant Isolation</h3>
                <p class="text-sm text-stone-500 leading-relaxed">
                    Establishments operate on isolated datasets. Managers configure physical layout capacities, rosters, menus, and booking requests independently.
                </p>
            </div>
 
            <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 p-6 rounded-2xl space-y-3">
                <h3 class="font-bold text-lg text-amber-700 dark:text-amber-400"><i class="fa-solid fa-credit-card mr-2"></i>Stripe Pre-Payments</h3>
                <p class="text-sm text-stone-500 leading-relaxed">
                    Prevent no-shows by collecting advance table reservation holdings or full seat prepayments directly through secure Stripe payment channels.
                </p>
            </div>
 
            <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 p-6 rounded-2xl space-y-3">
                <h3 class="font-bold text-lg text-amber-700 dark:text-amber-400"><i class="fa-solid fa-clipboard-user mr-2"></i>Staff Duty Roster</h3>
                <p class="text-sm text-stone-500 leading-relaxed">
                    Chefs and hosting waiters execute digital check-in and check-out attendance duty rosters complete with status logs and managers auditing panels.
                </p>
            </div>
 
            <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 p-6 rounded-2xl space-y-3">
                <h3 class="font-bold text-lg text-amber-700 dark:text-amber-400"><i class="fa-solid fa-file-invoice mr-2"></i>Automated PDF Invoices</h3>
                <p class="text-sm text-stone-500 leading-relaxed">
                    Instantly generate and distribute beautifully formatted PDF invoices directly to customer email inboxes upon processing reservation deposits.
                </p>
            </div>
        </div>
    </main>
 
    <!-- Footer -->
    <footer class="bg-stone-900 text-white py-12 border-t border-stone-800">
        <div class="max-w-7xl mx-auto px-6 text-center text-stone-500 text-xs">
            <p>&copy; {{ now()->year }} Zayka Dining. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
