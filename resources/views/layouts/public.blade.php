<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark"
    x-data="{
        darkMode: localStorage.getItem('zaykaTheme') !== 'light',
        toggleTheme() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('zaykaTheme', this.darkMode ? 'dark' : 'light');
            if (this.darkMode) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    }"
    x-init="
        if (localStorage.getItem('zaykaTheme') === 'light') {
            document.documentElement.classList.remove('dark');
            darkMode = false;
        } else {
            document.documentElement.classList.add('dark');
            darkMode = true;
        }
    "
>
    <head>
        @include('partials.head')

        <!-- Fonts & Icons -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
            .theme-toggle-btn {
                transition: all 0.2s ease;
            }
        </style>
    </head>
    <body class="bg-[#09090b] text-zinc-100 min-h-screen selection:bg-amber-600 selection:text-white transition-colors duration-300">
        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
