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
 
<div class="min-h-screen flex flex-col justify-between">
 
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
                <a href="{{ route('blogs') }}" class="text-amber-600 dark:text-amber-400 font-semibold">Blogs</a>
                <a href="{{ route('about') }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors">About Us</a>
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
    <main class="max-w-4xl mx-auto px-6 py-12 space-y-12">
        <div class="text-center space-y-4">
            <h1 class="text-4xl font-extrabold tracking-tight">Zayka Culinary Blogs</h1>
            <p class="text-stone-600 dark:text-stone-400 text-lg max-w-xl mx-auto">
                Explore secret recipes, spice mix details, and master chef logs from the finest kitchens on our platform.
            </p>
        </div>
 
        <div class="space-y-8">
            @forelse ($this->blogs as $blog)
                <article class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-3xl p-8 space-y-4 shadow-sm flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-stone-500">
                            <span class="text-amber-600 font-bold uppercase tracking-wider font-mono">{{ $blog->created_at->format('M d, Y') }}</span>
                            <span>•</span>
                            <a href="{{ route('restaurant.landing', ['id' => $blog->restaurant->id]) }}" class="hover:text-amber-600 flex items-center gap-1">
                                <i class="fa-solid fa-utensils"></i> {{ $blog->restaurant->name }}
                            </a>
                            <span>•</span>
                            <span class="text-amber-600"><i class="fa-solid fa-star"></i> {{ number_format($blog->restaurant->rating, 1) }}</span>
                        </div>
                        
                        <h2 class="text-2xl font-extrabold text-stone-900 dark:text-stone-50 hover:text-amber-600 transition-colors">
                            <a href="{{ route('restaurant.landing', ['id' => $blog->restaurant->id]) }}#blogs">{{ $blog->title }}</a>
                        </h2>
                        
                        <p class="text-stone-600 dark:text-stone-300 leading-relaxed text-base">{{ $blog->content }}</p>
                    </div>
 
                    <div class="border-t border-stone-100 dark:border-stone-800/60 pt-4 flex justify-between items-center">
                        <span class="text-xs text-stone-400">Published by: {{ $blog->restaurant->name }} Management</span>
                        <a href="{{ route('restaurant.landing', ['id' => $blog->restaurant->id]) }}" class="text-sm font-bold text-amber-600 hover:text-amber-700 flex items-center gap-1.5">
                            <span>Explore Restaurant</span>
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </a>
                    </div>
                </article>
            @empty
                <div class="text-center py-20 text-stone-400 bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-3xl">
                    <i class="fa-solid fa-feather-pointed text-4xl mb-3 text-stone-300"></i>
                    <p class="text-lg font-medium">{{ __('No culinary blogs have been published on the platform yet.') }}</p>
                </div>
            @endforelse
        </div>
    </main>
 
    <!-- Footer -->
    <footer class="bg-stone-900 text-white py-12 border-t border-stone-800">
        <div class="max-w-7xl mx-auto px-6 text-center text-stone-500 text-xs">
            <p>&copy; {{ now()->year }} Zayka Dining. All rights reserved.</p>
        </div>
    </footer>
</div>
