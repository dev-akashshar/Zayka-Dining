<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}</title>
    <meta name="description" content="Reserve tables at India's finest restaurants. Real-time table management, Stripe payments, and automated PDF invoices.">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    {{-- ── Google Fonts: Syne (headings) + Inter (body) + Playfair (accent) ── --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">

    {{-- ── Font Awesome 6 ── --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- ── Vite assets (Tailwind + JS) ── --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- ── Livewire head scripts ── --}}
    @livewireStyles

    {{-- ── Base reset for landing page only ── --}}
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --lp-font-head: 'Syne', sans-serif;
            --lp-font-body: 'Inter', sans-serif;
            --lp-font-serif: 'Playfair Display', serif;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: var(--lp-font-body) !important;
            background: #09090b;
            color: #f4f4f5;
            overflow-x: hidden;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body.lp-light {
            background: #f8fafc;
            color: #09090b;
        }

        /* Override Tailwind/Outfit defaults for this page */
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--lp-font-head) !important;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: #09090b; }
        ::-webkit-scrollbar-thumb { background: #27272a; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #3f3f46; }
    </style>

    {{-- ── Alpine.js (must load BEFORE @livewireScripts) ── --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body id="lp-body">
    {{ $slot }}

    {{-- Livewire scripts --}}
    @livewireScripts
</body>
</html>
