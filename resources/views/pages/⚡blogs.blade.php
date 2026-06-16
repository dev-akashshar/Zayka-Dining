<?php

use Livewire\Component;
use App\Models\Blog;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Title('Zayka Culinary Blogs'), Layout('layouts.public')] class extends Component {
    public function getBlogsProperty()
    {
        return Blog::with('restaurant')->latest()->get();
    }
}; ?>

<div class="min-h-screen flex flex-col justify-between bg-[#0b0b0c] text-stone-100">

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
                <a href="{{ route('blogs') }}" class="text-amber-400 font-semibold">Blogs</a>
                <a href="{{ route('about') }}" class="hover:text-amber-400 transition-colors">About Us</a>
                <a href="{{ route('contact') }}" class="hover:text-amber-400 transition-colors">Contact</a>
            </nav>

            <!-- Action buttons + Theme Toggle -->
            <div class="flex items-center gap-3">
                <!-- Theme Toggle Button -->
                <button
                    id="theme-toggle"
                    onclick="toggleZaykaTheme()"
                    class="p-2.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-stone-300 hover:text-amber-400 transition-all"
                    title="Toggle dark/light mode"
                >
                    <i class="fa-solid fa-sun text-sm sun-icon hidden"></i>
                    <i class="fa-solid fa-moon text-sm moon-icon"></i>
                </button>

                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 text-sm font-bold text-black bg-amber-500 hover:bg-amber-600 rounded-xl transition-all shadow-lg shadow-amber-500/20 flex items-center gap-2">
                        <span>Dashboard</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2.5 text-sm font-bold text-stone-300 hover:text-white transition-colors">
                        Log in
                    </a>
                    <a href="{{ route('register') }}" class="px-5 py-2.5 text-sm font-bold text-white bg-zinc-800 hover:bg-zinc-700 rounded-xl transition-all shadow-md border border-white/5">
                        Get Started
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main content -->
    <main class="max-w-4xl mx-auto px-6 py-16 space-y-12 flex-1">
        <div class="text-center space-y-4">
            <div class="inline-flex items-center gap-1.5 bg-amber-500/10 text-amber-400 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border border-amber-500/15">
                <i class="fa-solid fa-feather-pointed"></i> Culinary Chronicles
            </div>
            <h1 class="text-4xl sm:text-5xl font-black tracking-tight text-stone-100">Zayka Culinary Blogs</h1>
            <p class="text-stone-400 text-lg max-w-xl mx-auto leading-relaxed">
                Explore secret recipes, spice mix details, and master chef logs from the finest kitchens on our platform.
            </p>
        </div>

        <div class="space-y-8">
            @forelse ($this->blogs as $blog)
                <article class="bg-[#121214] border border-white/5 rounded-3xl p-8 space-y-4 hover:border-amber-500/20 transition-all duration-300 flex flex-col justify-between group">
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-stone-500">
                            <span class="text-amber-500 font-bold uppercase tracking-wider font-mono">{{ $blog->created_at->format('M d, Y') }}</span>
                            <span>•</span>
                            <a href="{{ route('restaurant.landing', ['id' => $blog->restaurant->id]) }}" class="hover:text-amber-400 flex items-center gap-1 transition-colors">
                                <i class="fa-solid fa-utensils text-amber-600"></i> {{ $blog->restaurant->name }}
                            </a>
                            <span>•</span>
                            <span class="text-amber-500"><i class="fa-solid fa-star"></i> {{ number_format($blog->restaurant->rating, 1) }}</span>
                        </div>
                        
                        <h2 class="text-2xl font-extrabold text-stone-100 group-hover:text-amber-400 transition-colors leading-snug">
                            <a href="{{ route('restaurant.landing', ['id' => $blog->restaurant->id]) }}#blogs">{{ $blog->title }}</a>
                        </h2>
                        
                        <p class="text-stone-400 leading-relaxed text-sm">{{ $blog->content }}</p>
                    </div>

                    <div class="border-t border-white/5 pt-4 flex justify-between items-center">
                        <span class="text-xs text-stone-600">Published by: {{ $blog->restaurant->name }} Management</span>
                        <a href="{{ route('restaurant.landing', ['id' => $blog->restaurant->id]) }}" class="text-sm font-bold text-amber-500 hover:text-amber-400 flex items-center gap-1.5 transition-colors">
                            <span>Explore Restaurant</span>
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </a>
                    </div>
                </article>
            @empty
                <div class="text-center py-20 text-stone-500 bg-[#121214] border border-white/5 rounded-3xl">
                    <i class="fa-solid fa-feather-pointed text-4xl mb-4 text-stone-700"></i>
                    <p class="text-lg font-medium">{{ __('No culinary blogs have been published on the platform yet.') }}</p>
                </div>
            @endforelse
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-black text-stone-300 py-12 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-6 text-center text-stone-600 text-xs">
            <p>&copy; {{ now()->year }} Zayka Dining. All rights reserved.</p>
        </div>
    </footer>
</div>

<script>
function toggleZaykaTheme() {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    if (isDark) {
        html.classList.remove('dark');
        localStorage.setItem('zaykaTheme', 'light');
        document.querySelector('.sun-icon').classList.add('hidden');
        document.querySelector('.moon-icon').classList.remove('hidden');
        document.body.style.backgroundColor = '#fafaf9';
        document.body.style.color = '#1c1917';
    } else {
        html.classList.add('dark');
        localStorage.setItem('zaykaTheme', 'dark');
        document.querySelector('.sun-icon').classList.remove('hidden');
        document.querySelector('.moon-icon').classList.add('hidden');
        document.body.style.backgroundColor = '#0b0b0c';
        document.body.style.color = '#f5f5f4';
    }
}
// Init on load
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
