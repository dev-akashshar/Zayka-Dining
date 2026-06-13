<?php
 
use Livewire\Component;
use App\Models\Restaurant;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
 
new #[Title('Restaurant Details'), Layout('layouts.public')] class extends Component {
    public int $restoId;
    public Restaurant $restaurant;
 
    public function mount(int $id): void
    {
        $this->restoId = $id;
        $this->restaurant = Restaurant::with(['menuItems', 'tables', 'blogs'])->findOrFail($id);
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
                <a href="{{ route('blogs') }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Blogs</a>
                <a href="{{ route('about') }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors">About Us</a>
                <a href="{{ route('contact') }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Contact</a>
            </nav>
 
            <!-- Action buttons -->
            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-xl transition-all shadow-md flex items-center gap-2">
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
            </div>
        </div>
    </header>
 
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-b from-amber-500/10 via-stone-50 to-stone-50 dark:from-amber-950/20 dark:via-stone-950 dark:to-stone-950 py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <!-- Left: Info -->
            <div class="lg:col-span-8 space-y-6">
                <div class="inline-flex items-center gap-1.5 bg-amber-500/10 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 px-3 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wider">
                    <i class="fa-solid fa-utensils"></i> {{ __('Premium Fine Dining') }}
                </div>
                <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-stone-900 dark:text-stone-50">
                    {{ $restaurant->name }}
                </h1>
                <p class="text-stone-600 dark:text-stone-300 max-w-2xl text-base leading-relaxed">
                    <i class="fa-solid fa-location-dot text-amber-600 mr-2"></i>{{ $restaurant->address }}
                </p>
                
                <div class="flex flex-wrap gap-6 text-sm font-medium">
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-phone text-amber-600"></i>{{ $restaurant->phone ?: __('Not Provided') }}</span>
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-envelope text-amber-600"></i>{{ $restaurant->email ?: __('Not Provided') }}</span>
                    <span class="flex items-center gap-1 text-amber-600"><i class="fa-solid fa-star"></i>{{ number_format($restaurant->rating, 1) }} ({{ $restaurant->reviews_count }} reviews)</span>
                </div>
 
                <div class="pt-4 flex gap-4">
                    <a href="{{ route('dashboard') }}#reserve-table" class="px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-amber-600/20 hover:scale-[1.02] flex items-center gap-2">
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Book Table Now</span>
                    </a>
                    <a href="#menu" class="px-6 py-3 bg-white dark:bg-stone-900 hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-800 dark:text-stone-200 border border-stone-200 dark:border-stone-800 rounded-xl font-semibold transition-all">
                        View Menu
                    </a>
                </div>
            </div>
            
            <!-- Right: Photo -->
            <div class="lg:col-span-4">
                <div class="relative max-w-sm mx-auto bg-white dark:bg-stone-900 rounded-2xl overflow-hidden border border-neutral-200 dark:border-stone-800 shadow-xl p-4">
                    @if ($restaurant->image_path)
                        <img src="{{ asset('storage/' . $restaurant->image_path) }}" alt="{{ $restaurant->name }}" class="w-full h-56 object-cover rounded-xl" />
                    @else
                        <div class="w-full h-56 bg-gradient-to-br from-amber-500 to-orange-700 rounded-xl flex flex-col items-center justify-center text-white gap-3">
                            <i class="fa-solid fa-bowl-food text-5xl"></i>
                            <span class="font-serif-indian italic text-lg font-semibold">Zayka Experience</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
 
    <!-- Tabs / Info sections -->
    <main class="max-w-7xl mx-auto px-6 py-12 grid grid-cols-1 lg:grid-cols-12 gap-12">
        <!-- Main Area: Menu & Blogs (8 cols) -->
        <div class="lg:col-span-8 space-y-12">
            <!-- Menu Section -->
            <section id="menu" class="space-y-6">
                <div class="flex items-center justify-between border-b border-stone-200 dark:border-stone-800 pb-3">
                    <h2 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-amber-600"></i>
                        {{ __('Our Royal Menu') }}
                    </h2>
                </div>
 
                @php
                    $menuByCategory = $restaurant->menuItems->groupBy('category');
                @endphp
 
                @forelse ($menuByCategory as $category => $items)
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold text-amber-700 dark:text-amber-400 border-l-4 border-amber-600 pl-3 uppercase tracking-wider text-sm">{{ $category }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($items as $item)
                                <div class="bg-white dark:bg-stone-900 border border-stone-100 dark:border-stone-800/80 rounded-2xl p-4 flex justify-between gap-4">
                                    <div class="space-y-1">
                                        <h4 class="font-bold text-stone-800 dark:text-stone-100 text-sm md:text-base">{{ $item->name }}</h4>
                                        <p class="text-xs text-stone-500 dark:text-stone-400 line-clamp-2">{{ $item->description }}</p>
                                    </div>
                                    <div class="text-right flex flex-col justify-between items-end min-w-[80px]">
                                        <span class="font-bold text-amber-600 text-sm md:text-base">₹{{ number_format($item->price, 2) }}</span>
                                        @if (!$item->is_available)
                                            <span class="text-[10px] text-red-500 font-bold bg-red-500/10 px-1.5 py-0.5 rounded-full uppercase">{{ __('Out of Stock') }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-stone-400 bg-white dark:bg-stone-900 border border-stone-100 dark:border-stone-800 rounded-2xl">
                        <i class="fa-solid fa-utensils text-3xl mb-3 text-stone-300"></i>
                        <p>{{ __('No menu items available at the moment.') }}</p>
                    </div>
                @endforelse
            </section>
 
            <!-- Blogs Section -->
            <section id="blogs" class="space-y-6">
                <div class="flex items-center justify-between border-b border-stone-200 dark:border-stone-800 pb-3">
                    <h2 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-feather-pointed text-amber-600"></i>
                        {{ __('Restaurant Culinary Blogs') }}
                    </h2>
                </div>
 
                <div class="space-y-4">
                    @forelse ($restaurant->blogs as $blog)
                        <div class="bg-white dark:bg-stone-900 border border-stone-100 dark:border-stone-800 rounded-2xl p-6 space-y-3 shadow-xs">
                            <span class="text-xs text-amber-600 font-semibold uppercase tracking-wider font-mono">{{ $blog->created_at->format('M d, Y') }}</span>
                            <h3 class="text-xl font-bold text-stone-800 dark:text-stone-100">{{ $blog->title }}</h3>
                            <p class="text-sm text-stone-600 dark:text-stone-400 leading-relaxed line-clamp-3">{{ $blog->content }}</p>
                        </div>
                    @empty
                        <div class="text-center py-12 text-stone-400 bg-white dark:bg-stone-900 border border-stone-100 dark:border-stone-800 rounded-2xl">
                            <i class="fa-solid fa-pen-nib text-3xl mb-3 text-stone-300"></i>
                            <p>{{ __('No blogs published yet by this restaurant.') }}</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
 
        <!-- Side Panel: Table Listings (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-3xl p-6 shadow-xs space-y-4">
                <h3 class="font-bold text-lg text-stone-900 dark:text-stone-50 border-b border-stone-100 dark:border-stone-850 pb-2 flex items-center gap-2">
                    <i class="fa-solid fa-chair text-amber-600 text-sm"></i>
                    {{ __('Seating Tables Layout') }}
                </h3>
                <div class="space-y-3">
                    @forelse ($restaurant->tables as $table)
                        <div class="flex justify-between items-center bg-stone-50 dark:bg-stone-950 p-3 rounded-xl border border-stone-100 dark:border-stone-800">
                            <div>
                                <span class="font-bold text-sm block">{{ $table->name }}</span>
                                <span class="text-xs text-stone-500">{{ __('Capacity:') }} {{ $table->capacity }} {{ __('guests') }}</span>
                            </div>
                            <flux:badge color="{{ $table->status === 'available' ? 'green' : 'red' }}" size="sm">
                                {{ $table->status }}
                            </flux:badge>
                        </div>
                    @empty
                        <div class="text-center py-6 text-stone-400">
                            <p class="text-sm">{{ __('No tables assigned yet.') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
            
            <div class="bg-gradient-to-tr from-amber-600 to-orange-700 text-white rounded-3xl p-6 shadow-xl space-y-4">
                <h4 class="font-bold text-lg">Taste the Royal Heritage</h4>
                <p class="text-xs text-amber-100 leading-relaxed">
                    Choose from standard tables, luxury Maharaja seats, or spiced terrace dining setups. Instantly verify availability and book your seats securely.
                </p>
                <a href="{{ route('dashboard') }}#reserve-table" class="w-full text-center py-3 bg-white text-amber-700 font-semibold rounded-xl block hover:bg-stone-50 shadow-md">Book a Table</a>
            </div>
        </div>
    </main>
 
    <!-- Footer -->
    <footer class="bg-stone-900 text-white py-12 border-t border-stone-800">
        <div class="max-w-7xl mx-auto px-6 text-center text-stone-500 text-xs">
            <p>&copy; {{ now()->year }} Zayka Dining. All rights reserved.</p>
        </div>
    </footer>
</div>
