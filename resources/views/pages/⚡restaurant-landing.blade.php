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
                <a href="{{ route('blogs') }}" class="hover:text-amber-400 transition-colors">Blogs</a>
                <a href="{{ route('about') }}" class="hover:text-amber-400 transition-colors">About Us</a>
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
                        <span>Go to Dashboard</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2.5 text-sm font-bold text-stone-300 hover:text-white transition-colors">Log in</a>
                    <a href="{{ route('register') }}" class="px-5 py-2.5 text-sm font-bold text-white bg-zinc-800 hover:bg-zinc-700 rounded-xl transition-all shadow-md border border-white/5">Get Started</a>
                @endauth
            </div>
        </div>
    </header>
 
    <!-- Hero Section -->
    <section class="relative py-14 lg:py-20 border-b border-white/5">
        <!-- Ambient glow -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[400px] bg-amber-500/5 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-12 gap-10 items-center relative">
            <!-- Left: Info -->
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-1.5 bg-amber-500/10 text-amber-400 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border border-amber-500/15">
                    <i class="fa-solid fa-utensils"></i> {{ __('Premium Fine Dining') }}
                </div>
                <h1 class="text-4xl sm:text-5xl font-black tracking-tight text-stone-100 leading-tight">
                    {{ $restaurant->name }}
                </h1>
                <p class="text-stone-400 max-w-2xl text-base leading-relaxed">
                    <i class="fa-solid fa-location-dot text-amber-500 mr-2"></i>{{ $restaurant->address }}
                </p>
                
                <div class="flex flex-wrap gap-6 text-sm font-medium text-stone-300">
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-phone text-amber-500"></i>{{ $restaurant->phone ?: __('Not Provided') }}</span>
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-envelope text-amber-500"></i>{{ $restaurant->email ?: __('Not Provided') }}</span>
                    <span class="flex items-center gap-1 text-amber-400 font-bold"><i class="fa-solid fa-star"></i>{{ number_format($restaurant->rating, 1) }} <span class="text-stone-500 font-normal">({{ $restaurant->reviews_count }} reviews)</span></span>
                </div>
 
                <div class="pt-2 flex flex-wrap gap-4">
                    <a href="{{ route('dashboard') }}#reserve-table" class="px-6 py-3.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-black font-extrabold rounded-xl transition-all shadow-lg shadow-amber-500/25 hover:scale-[1.02] flex items-center gap-2">
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Book Table Now</span>
                    </a>
                    <a href="#menu" class="px-6 py-3.5 bg-zinc-900 hover:bg-zinc-800 text-stone-200 border border-white/5 rounded-xl font-bold transition-all flex items-center gap-2">
                        <i class="fa-solid fa-list text-amber-500 text-sm"></i>
                        View Menu
                    </a>
                </div>
            </div>
            
            <!-- Right: Photo -->
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-sm bg-[#121214] rounded-3xl overflow-hidden border border-white/5 shadow-2xl hover:border-amber-500/20 transition-all duration-300" style="box-shadow: 0 0 50px -10px rgba(217,119,6,0.2);">
                    @if ($restaurant->image_path)
                        <img src="{{ asset('storage/' . $restaurant->image_path) }}" alt="{{ $restaurant->name }}" class="w-full h-64 object-cover" />
                    @else
                        <div class="w-full h-64 bg-gradient-to-br from-amber-600 to-orange-800 flex flex-col items-center justify-center text-white gap-3">
                            <i class="fa-solid fa-bowl-food text-5xl opacity-60"></i>
                            <span class="font-serif-indian italic text-lg font-semibold opacity-80">Zayka Experience</span>
                        </div>
                    @endif
                    <div class="p-5 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-stone-100">{{ $restaurant->name }}</span>
                            <span class="text-amber-400 font-bold flex items-center gap-1 text-sm"><i class="fa-solid fa-star text-xs"></i>{{ number_format($restaurant->rating, 1) }}</span>
                        </div>
                        <p class="text-xs text-stone-500 flex items-center gap-1.5"><i class="fa-solid fa-map-pin text-amber-600"></i>{{ $restaurant->address }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
 
    <!-- Tabs / Info sections -->
    <main class="max-w-7xl mx-auto px-6 py-14 grid grid-cols-1 lg:grid-cols-12 gap-12 flex-1">
        <!-- Main Area: Menu & Blogs (8 cols) -->
        <div class="lg:col-span-8 space-y-14">
            <!-- Menu Section -->
            <section id="menu" class="space-y-8">
                <div class="flex items-center justify-between border-b border-white/5 pb-4">
                    <h2 class="text-2xl font-black tracking-tight flex items-center gap-2 text-stone-100">
                        <i class="fa-solid fa-list-check text-amber-500"></i>
                        {{ __('Our Royal Menu') }}
                    </h2>
                </div>
 
                @php
                    $menuByCategory = $restaurant->menuItems->groupBy('category');
                @endphp
 
                @forelse ($menuByCategory as $category => $items)
                    <div class="space-y-4">
                        <h3 class="text-xs font-black text-amber-400 border-l-4 border-amber-600 pl-3 uppercase tracking-wider">{{ $category }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($items as $item)
                                <div class="bg-[#121214] border border-white/5 rounded-2xl p-4 flex justify-between gap-4 hover:border-amber-500/15 transition-all">
                                    <div class="space-y-1">
                                        <h4 class="font-bold text-stone-100 text-sm md:text-base">{{ $item->name }}</h4>
                                        <p class="text-xs text-stone-500 line-clamp-2">{{ $item->description }}</p>
                                    </div>
                                    <div class="text-right flex flex-col justify-between items-end min-w-[80px]">
                                        <span class="font-bold text-amber-400 text-sm md:text-base">₹{{ number_format($item->price, 2) }}</span>
                                        @if (!$item->is_available)
                                            <span class="text-[10px] text-red-400 font-bold bg-red-500/10 px-1.5 py-0.5 rounded-full uppercase border border-red-500/20">{{ __('Out of Stock') }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-stone-500 bg-[#121214] border border-white/5 rounded-2xl">
                        <i class="fa-solid fa-utensils text-3xl mb-3 text-stone-700"></i>
                        <p>{{ __('No menu items available at the moment.') }}</p>
                    </div>
                @endforelse
            </section>
 
            <!-- Blogs Section -->
            <section id="blogs" class="space-y-6">
                <div class="flex items-center justify-between border-b border-white/5 pb-4">
                    <h2 class="text-2xl font-black tracking-tight flex items-center gap-2 text-stone-100">
                        <i class="fa-solid fa-feather-pointed text-amber-500"></i>
                        {{ __('Restaurant Culinary Blogs') }}
                    </h2>
                </div>
 
                <div class="space-y-4">
                    @forelse ($restaurant->blogs as $blog)
                        <div class="bg-[#121214] border border-white/5 rounded-2xl p-6 space-y-3 hover:border-amber-500/15 transition-all">
                            <span class="text-xs text-amber-500 font-bold uppercase tracking-wider font-mono">{{ $blog->created_at->format('M d, Y') }}</span>
                            <h3 class="text-xl font-bold text-stone-100">{{ $blog->title }}</h3>
                            <p class="text-sm text-stone-400 leading-relaxed line-clamp-3">{{ $blog->content }}</p>
                        </div>
                    @empty
                        <div class="text-center py-12 text-stone-500 bg-[#121214] border border-white/5 rounded-2xl">
                            <i class="fa-solid fa-pen-nib text-3xl mb-3 text-stone-700"></i>
                            <p>{{ __('No blogs published yet by this restaurant.') }}</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
 
        <!-- Side Panel: Table Listings (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-[#121214] border border-white/5 rounded-3xl p-6 shadow-xl space-y-4 hover:border-amber-500/10 transition-all sticky top-28">
                <h3 class="font-black text-lg text-stone-100 border-b border-white/5 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-chair text-amber-500 text-sm"></i>
                    {{ __('Seating Tables Layout') }}
                </h3>
                <div class="space-y-3">
                    @forelse ($restaurant->tables as $table)
                        <div class="flex justify-between items-center bg-[#0b0b0c] p-3 rounded-xl border border-white/5">
                            <div>
                                <span class="font-bold text-sm block text-stone-200">{{ $table->name }}</span>
                                <span class="text-xs text-stone-500">{{ __('Capacity:') }} {{ $table->capacity }} {{ __('guests') }}</span>
                            </div>
                            <span class="text-[10px] font-extrabold px-2.5 py-1 rounded-full uppercase border {{ $table->status === 'available' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20' }}">
                                {{ $table->status }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-6 text-stone-600">
                            <p class="text-sm">{{ __('No tables assigned yet.') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
            
            <div class="bg-gradient-to-tr from-amber-600 to-orange-700 text-white rounded-3xl p-6 shadow-xl space-y-4" style="box-shadow: 0 0 50px -10px rgba(217,119,6,0.3);">
                <h4 class="font-black text-lg">Taste the Royal Heritage</h4>
                <p class="text-xs text-amber-100 leading-relaxed">
                    Choose from standard tables, luxury Maharaja seats, or spiced terrace dining setups. Instantly verify availability and book your seats securely.
                </p>
                <a href="{{ route('dashboard') }}#reserve-table" class="w-full text-center py-3.5 bg-black/20 hover:bg-black/30 backdrop-blur-md text-white font-bold rounded-xl block transition-all border border-white/20">
                    Book a Table
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
</div>

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
