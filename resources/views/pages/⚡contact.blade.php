<?php
 
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Flux\Flux;
 
new #[Title('Contact Us'), Layout('layouts.public')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $message = '';
 
    public function sendMessage(): void
    {
        $this->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email',
            'message' => 'required|string|min:10',
        ]);
 
        // Mock sending message
        Flux::toast(variant: 'success', text: __('Namaste, your message has been received! We will contact you shortly.'));
        
        $this->reset(['name', 'email', 'message']);
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
                <a href="{{ route('contact') }}" class="text-amber-400 font-semibold">Contact</a>
            </nav>
 
            <!-- Action buttons + Theme Toggle -->
            <div class="flex items-center gap-3">
                <button onclick="toggleZaykaTheme()" class="p-2.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-stone-300 hover:text-amber-400 transition-all" title="Toggle theme">
                    <i class="fa-solid fa-sun text-sm sun-icon hidden"></i>
                    <i class="fa-solid fa-moon text-sm moon-icon"></i>
                </button>
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 text-sm font-bold text-black bg-amber-500 hover:bg-amber-600 rounded-xl transition-all shadow-lg shadow-amber-500/20 flex items-center gap-2">
                        <span>Dashboard</span><i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2.5 text-sm font-bold text-stone-300 hover:text-white transition-colors">Log in</a>
                    <a href="{{ route('register') }}" class="px-5 py-2.5 text-sm font-bold text-white bg-zinc-800 hover:bg-zinc-700 rounded-xl transition-all shadow-md border border-white/5">Get Started</a>
                @endauth
            </div>
        </div>
    </header>
 
    <!-- Main content -->
    <main class="max-w-lg mx-auto px-6 py-16 space-y-10 flex-1">
        <div class="text-center space-y-4">
            <div class="inline-flex items-center gap-1.5 bg-amber-500/10 text-amber-400 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border border-amber-500/15">
                <i class="fa-solid fa-envelope"></i> Get In Touch
            </div>
            <h1 class="text-4xl sm:text-5xl font-black tracking-tight text-stone-100">Contact Us</h1>
            <p class="text-stone-400 text-sm leading-relaxed">
                Have questions about restaurant settings, billing, or technical queries? Send us a message.
            </p>
        </div>
 
        <div class="bg-[#121214] border border-white/5 rounded-3xl p-8 shadow-xl hover:border-amber-500/10 transition-all">
            <form wire:submit="sendMessage" class="space-y-5">
                <flux:input 
                    wire:model="name"
                    name="name"
                    :label="__('Full Name')"
                    type="text"
                    required
                    placeholder="John Doe"
                />
 
                <flux:input 
                    wire:model="email"
                    name="email"
                    :label="__('Email Address')"
                    type="email"
                    required
                    placeholder="you@example.com"
                />
 
                <flux:textarea 
                    wire:model="message"
                    name="message"
                    :label="__('Your Message')"
                    required
                    placeholder="Ask us anything..."
                    rows="5"
                />
 
                <flux:button variant="primary" type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-black font-extrabold">
                    {{ __('Send Message') }}
                </flux:button>
            </form>
        </div>

        <!-- Contact Info Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
            <div class="bg-[#121214] border border-white/5 rounded-2xl p-5 space-y-2">
                <i class="fa-solid fa-envelope text-amber-500 text-xl"></i>
                <p class="text-xs text-stone-500 font-bold uppercase tracking-wider">Email</p>
                <p class="text-xs text-stone-300">support@zaykadining.com</p>
            </div>
            <div class="bg-[#121214] border border-white/5 rounded-2xl p-5 space-y-2">
                <i class="fa-solid fa-phone text-amber-500 text-xl"></i>
                <p class="text-xs text-stone-500 font-bold uppercase tracking-wider">Phone</p>
                <p class="text-xs text-stone-300">+91 11-45000000</p>
            </div>
            <div class="bg-[#121214] border border-white/5 rounded-2xl p-5 space-y-2">
                <i class="fa-solid fa-location-dot text-amber-500 text-xl"></i>
                <p class="text-xs text-stone-500 font-bold uppercase tracking-wider">Office</p>
                <p class="text-xs text-stone-300">New Delhi, India</p>
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
