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
                <a href="{{ route('contact') }}" class="text-amber-600 dark:text-amber-400 font-semibold">Contact</a>
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
    <main class="max-w-lg mx-auto px-6 py-12 space-y-8">
        <div class="text-center space-y-4">
            <h1 class="text-4xl font-extrabold tracking-tight">Contact Us</h1>
            <p class="text-stone-600 dark:text-stone-400 text-sm">
                Have questions about restaurant settings, billing, or technical queries? Send us a message.
            </p>
        </div>
 
        <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-3xl p-8 shadow-sm">
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
                />
 
                <flux:button variant="primary" type="submit" class="w-full bg-amber-600 hover:bg-amber-700">
                    {{ __('Send Message') }}
                </flux:button>
            </form>
        </div>
    </main>
 
    <!-- Footer -->
    <footer class="bg-stone-900 text-white py-12 border-t border-stone-800">
        <div class="max-w-7xl mx-auto px-6 text-center text-stone-500 text-xs">
            <p>&copy; {{ now()->year }} Zayka Dining. All rights reserved.</p>
        </div>
    </footer>
    
</div>
