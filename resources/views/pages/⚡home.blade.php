<?php

use Livewire\Component;
use App\Models\Restaurant;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Title('Zayka Dining — Premium Indian Restaurant Reservation'), Layout('layouts.public')] class extends Component
{
    public string $searchQuery = '';

    public function getRestaurantsProperty()
    {
        return Restaurant::where('is_active', true)
            ->when($this->searchQuery, fn($q) => $q->where('name', 'like', "%{$this->searchQuery}%")
                ->orWhere('address', 'like', "%{$this->searchQuery}%"))
            ->get();
    }
}; ?>

{{-- ══════════════════════════════════════════════════════════════════════
     ZAYKA DINING — LANDING PAGE (Livewire Component)
     Uses layouts.public which handles: fonts, FA icons, Alpine.js, Flux
══════════════════════════════════════════════════════════════════════ --}}
<div x-data="{ theme: localStorage.getItem('zaykaTheme') || 'dark' }" class="min-h-screen">

<!-- ═══════════════════════ INLINE LANDING STYLES ═══════════════════════ -->
<style>
/* ── Base ── */
.lp-bg { background-color: #09090b; color: #f4f4f5; }
.lp-bg.light { background-color: #f8fafc; color: #09090b; }

/* ── Animated shimmer gradient text ── */
.grad-text {
    background: linear-gradient(135deg, #f59e0b 0%, #ef4444 40%, #f59e0b 80%);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: lp-shimmer 5s linear infinite;
}
@keyframes lp-shimmer { to { background-position: 200% center; } }

/* ── Ambient glow blobs ── */
.lp-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(120px);
    pointer-events: none;
    z-index: 0;
}

/* ── Section badge / eyebrow ── */
.lp-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(245,158,11,0.1);
    border: 1px solid rgba(245,158,11,0.25);
    color: #f59e0b;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    padding: 6px 16px;
    border-radius: 999px;
}

/* ── Floating nav pill ── */
.lp-nav {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 100;
    padding: 14px 24px;
}
.lp-nav-pill {
    max-width: 1100px;
    margin: 0 auto;
    height: 62px;
    background: rgba(9,9,11,0.75);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    transition: background 0.3s ease;
}
.light .lp-nav-pill {
    background: rgba(248,250,252,0.85);
    border-color: rgba(0,0,0,0.08);
}

/* ── Buttons ── */
.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 13px 28px;
    border-radius: 14px;
    font-weight: 700;
    font-size: 14px;
    background: linear-gradient(135deg, #f59e0b, #ea580c);
    color: #000;
    transition: all 0.3s ease;
    box-shadow: 0 4px 24px -4px rgba(245,158,11,0.35);
    cursor: pointer;
    border: none;
    text-decoration: none;
}
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 32px -4px rgba(245,158,11,0.55);
}
.btn-outline {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 13px 28px;
    border-radius: 14px;
    font-weight: 600;
    font-size: 14px;
    background: transparent;
    border: 1px solid rgba(255,255,255,0.12);
    color: #f4f4f5;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
}
.light .btn-outline { border-color: rgba(0,0,0,0.15); color: #09090b; }
.btn-outline:hover { background: rgba(255,255,255,0.06); transform: translateY(-2px); }
.light .btn-outline:hover { background: rgba(0,0,0,0.06); }

/* ── Card base ── */
.lp-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 20px;
    transition: all 0.35s cubic-bezier(0.34,1.56,0.64,1);
    overflow: hidden;
}
.light .lp-card { background: #fff; border-color: rgba(0,0,0,0.08); }
.lp-card:hover { border-color: rgba(245,158,11,0.3); transform: translateY(-6px); box-shadow: 0 24px 48px -12px rgba(0,0,0,0.5), 0 0 0 1px rgba(245,158,11,0.08); }
.light .lp-card:hover { box-shadow: 0 24px 48px -12px rgba(0,0,0,0.12), 0 0 0 1px rgba(245,158,11,0.15); }

/* ── Restaurant card image hover zoom ── */
.lp-card .card-img-wrap img { transition: transform 0.6s cubic-bezier(0.25,0.46,0.45,0.94); }
.lp-card:hover .card-img-wrap img { transform: scale(1.07); }

/* ── Feature / info card ── */
.feat-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 20px;
    padding: 32px 28px;
    transition: all 0.3s ease;
}
.light .feat-card { background: #fff; border-color: rgba(0,0,0,0.08); }
.feat-card:hover { border-color: rgba(245,158,11,0.25); box-shadow: 0 0 0 1px rgba(245,158,11,0.08), 0 16px 40px -12px rgba(0,0,0,0.4); }
.light .feat-card:hover { box-shadow: 0 0 0 1px rgba(245,158,11,0.15), 0 16px 40px -12px rgba(0,0,0,0.08); }

/* ── Icon box ── */
.icon-box {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(245,158,11,0.12), rgba(234,88,12,0.06));
    border: 1px solid rgba(245,158,11,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 24px;
    font-size: 22px;
    color: #f59e0b;
    flex-shrink: 0;
}

/* ── Step circle ── */
.step-circle {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(245,158,11,0.15), rgba(234,88,12,0.08));
    border: 1px solid rgba(245,158,11,0.25);
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 800; color: #f59e0b;
    flex-shrink: 0;
    font-family: 'Syne', sans-serif;
}

/* ── Price card ── */
.price-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 24px;
    padding: 36px 32px;
    transition: all 0.3s ease;
}
.light .price-card { background: #fff; border-color: rgba(0,0,0,0.08); }
.price-card.featured {
    background: linear-gradient(160deg, rgba(245,158,11,0.07) 0%, rgba(234,88,12,0.04) 100%);
    border-color: rgba(245,158,11,0.35);
    box-shadow: 0 0 60px -20px rgba(245,158,11,0.2);
}

/* ── Quote card ── */
.quote-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 20px;
    padding: 36px 32px;
    position: relative;
    overflow: hidden;
}
.light .quote-card { background: #fff; border-color: rgba(0,0,0,0.08); }
.quote-card::before {
    content: '\201C';
    font-family: 'Playfair Display', serif;
    font-size: 120px;
    line-height: 1;
    color: rgba(245,158,11,0.08);
    position: absolute;
    top: -10px; left: 20px;
}

/* ── Divider line ── */
.lp-divider {
    border: none;
    border-top: 1px solid rgba(255,255,255,0.06);
    margin: 0;
}
.light .lp-divider { border-top-color: rgba(0,0,0,0.07); }

/* ── Stat numbers ── */
.stat-val {
    font-size: clamp(2rem, 4vw, 2.8rem);
    font-weight: 800;
    background: linear-gradient(135deg, #fbbf24, #f97316);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
    font-family: 'Syne', sans-serif;
    line-height: 1;
}
.stat-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    color: #71717a;
    margin-top: 6px;
}

/* ── Marquee ticker ── */
.marquee-track {
    display: flex;
    gap: 40px;
    animation: lp-marquee 25s linear infinite;
    width: max-content;
}
@keyframes lp-marquee {
    from { transform: translateX(0); }
    to { transform: translateX(-50%); }
}

/* ── Rating stars ── */
.stars { color: #f59e0b; font-size: 11px; }

/* ── Cuisine tag ── */
.cuisine-tag {
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    background: rgba(0,0,0,0.65);
    color: #fbbf24;
    border: 1px solid rgba(245,158,11,0.3);
    backdrop-filter: blur(8px);
    padding: 4px 10px;
    border-radius: 999px;
}

/* ── Rating badge ── */
.rating-badge {
    display: flex; align-items: center; gap: 4px;
    background: rgba(0,0,0,0.65);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(245,158,11,0.2);
    color: #fbbf24;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 999px;
}

/* ── Scroll animations ── */
.scroll-fade { opacity: 0; transform: translateY(28px); transition: opacity 0.65s ease, transform 0.65s ease; }
.scroll-fade.in { opacity: 1; transform: translateY(0); }

/* ── Mobile menu toggle ── */
.mob-menu { display: none; }
.mob-menu.open { display: block; }

/* ── Nav links ── */
.nav-link { font-size: 13.5px; font-weight: 500; color: #a1a1aa; padding: 7px 14px; border-radius: 10px; transition: all 0.2s; text-decoration: none; }
.nav-link:hover { color: #f4f4f5; background: rgba(255,255,255,0.05); }
.light .nav-link { color: #52525b; }
.light .nav-link:hover { color: #09090b; background: rgba(0,0,0,0.05); }

/* ── Muted text ── */
.lp-muted { color: #71717a; }
.light .lp-muted { color: #52525b; }
.lp-subtle { color: #52525b; }
.light .lp-subtle { color: #a1a1aa; }

/* ── Search input ── */
.search-input {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 14px;
    padding: 13px 18px 13px 46px;
    color: #f4f4f5;
    font-size: 14px;
    width: 100%;
    max-width: 400px;
    transition: all 0.2s;
    outline: none;
}
.light .search-input { background: rgba(0,0,0,0.04); border-color: rgba(0,0,0,0.1); color: #09090b; }
.search-input:focus { border-color: rgba(245,158,11,0.4); background: rgba(245,158,11,0.04); }
.search-input::placeholder { color: #52525b; }
.light .search-input::placeholder { color: #a1a1aa; }

/* ── CTA section bg ── */
.cta-section {
    background: linear-gradient(135deg, rgba(245,158,11,0.06) 0%, rgba(234,88,12,0.03) 100%);
    border: 1px solid rgba(245,158,11,0.12);
    border-radius: 28px;
}
.light .cta-section { background: linear-gradient(135deg, rgba(245,158,11,0.06) 0%, rgba(234,88,12,0.03) 100%); }

/* ── Mock dashboard ── */
.mock-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 16px;
    padding: 14px 16px;
}
.light .mock-card { background: rgba(0,0,0,0.02); border-color: rgba(0,0,0,0.06); }

/* ── Footer ── */
.lp-footer { background: #060606; border-top: 1px solid rgba(255,255,255,0.05); }
.light .lp-footer { background: #f1f5f9; border-top-color: rgba(0,0,0,0.07); }

/* ── Responsive helpers ── */
@media (max-width: 768px) {
    .hide-mobile { display: none !important; }
    .lp-hero-h1 { font-size: clamp(2.6rem, 10vw, 4rem); }
}
@media (min-width: 769px) {
    .show-mobile { display: none !important; }
}
</style>

{{-- ───────────────────────────── WRAPPER ───────────────────────────── --}}
<div id="lp-root" class="lp-bg" :class="{ 'light': theme === 'light' }">

    {{-- ═══ NAV ═══ --}}
    <div class="lp-nav">
        <div class="lp-nav-pill" :class="{ 'light': theme === 'light' }">
            {{-- Logo --}}
            <a href="/" class="flex items-center gap-2.5 flex-shrink-0" style="text-decoration:none;">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg" style="box-shadow:0 4px 16px -4px rgba(245,158,11,0.5)">
                    <i class="fa-solid fa-bowl-rice text-black text-sm"></i>
                </div>
                <span style="font-family:'Syne',sans-serif; font-weight:800; font-size:17px; letter-spacing:-0.02em;">
                    Zayka <em style="font-family:'Playfair Display',serif; font-style:italic; color:#f59e0b;">Dining</em>
                </span>
            </a>

            {{-- Desktop nav --}}
            <nav class="hide-mobile flex items-center gap-1">
                <a href="#restaurants" class="nav-link">Restaurants</a>
                <a href="#features" class="nav-link">Features</a>
                <a href="#how" class="nav-link">How it Works</a>
                <a href="#pricing" class="nav-link">Pricing</a>
            </nav>

            {{-- Right actions --}}
            <div class="flex items-center gap-2.5">
                {{-- Theme toggle --}}
                <button
                    @click="theme = (theme === 'dark') ? 'light' : 'dark'; localStorage.setItem('zaykaTheme', theme)"
                    class="w-9 h-9 rounded-xl flex items-center justify-center transition-all"
                    style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); color:#a1a1aa;"
                    title="Toggle theme"
                >
                    <i class="fa-solid text-sm" :class="theme === 'dark' ? 'fa-moon' : 'fa-sun'" style="color:#f59e0b;"></i>
                </button>

                @if(Route::has('login'))
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary hide-mobile" style="font-size:13px; padding:10px 22px;">
                            Dashboard <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="nav-link hide-mobile" style="font-weight:600;">Sign in</a>
                        <a href="{{ route('register') }}" class="btn-primary" style="font-size:13px; padding:10px 22px;">
                            Get Started <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    @endauth
                @endif

                {{-- Mobile hamburger --}}
                <button class="show-mobile w-9 h-9 rounded-xl flex items-center justify-center" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); color:#a1a1aa;"
                    onclick="document.getElementById('mob-nav').classList.toggle('open')">
                    <i class="fa-solid fa-bars text-sm"></i>
                </button>
            </div>
        </div>

        {{-- Mobile dropdown --}}
        <div id="mob-nav" class="mob-menu" style="max-width:1100px; margin:8px auto 0; background:rgba(9,9,11,0.95); backdrop-filter:blur(24px); border:1px solid rgba(255,255,255,0.07); border-radius:18px; padding:16px 20px;">
            <div class="space-y-1">
                <a href="#restaurants" class="nav-link block" onclick="document.getElementById('mob-nav').classList.remove('open')">Restaurants</a>
                <a href="#features" class="nav-link block" onclick="document.getElementById('mob-nav').classList.remove('open')">Features</a>
                <a href="#how" class="nav-link block" onclick="document.getElementById('mob-nav').classList.remove('open')">How it Works</a>
                <a href="#pricing" class="nav-link block" onclick="document.getElementById('mob-nav').classList.remove('open')">Pricing</a>
            </div>
            <div class="mt-4 pt-4 flex gap-3" style="border-top:1px solid rgba(255,255,255,0.06);">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary flex-1 justify-center">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-outline flex-1 justify-center">Sign in</a>
                    <a href="{{ route('register') }}" class="btn-primary flex-1 justify-center">Get Started</a>
                @endauth
            </div>
        </div>
    </div>
    {{-- /NAV --}}

    {{-- ═══════════════════════════ HERO SECTION ═══════════════════════════ --}}
    <section style="position:relative; min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; padding: 140px 24px 80px; overflow:hidden;">
        {{-- Ambient blobs --}}
        <div class="lp-blob" style="width:700px; height:700px; background:rgba(245,158,11,0.07); top:10%; left:50%; transform:translateX(-50%);"></div>
        <div class="lp-blob" style="width:400px; height:400px; background:rgba(234,88,12,0.05); bottom:20%; right:5%;"></div>

        <div style="position:relative; z-index:1; max-width:900px; margin:0 auto; text-align:center;">

            @if(session('status') === 'manager-request-submitted')
                <div style="background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); border-radius:14px; padding:14px 20px; margin-bottom:32px; color:#34d399; font-size:14px; display:flex; align-items:center; gap:10px; text-align:left;">
                    <i class="fa-solid fa-circle-check"></i>
                    {{ __('Registration submitted. Admin will approve your restaurant shortly.') }}
                </div>
            @endif

            {{-- Eyebrow --}}
            <div class="lp-badge scroll-fade" style="margin-bottom:28px; display:inline-flex;">
                <span style="width:7px; height:7px; border-radius:50%; background:#f59e0b; animation: pulse 2s infinite;"></span>
                India's #1 Restaurant Reservation Platform
            </div>

            {{-- Headline --}}
            <h1 class="lp-hero-h1 scroll-fade" style="font-family:'Syne',sans-serif; font-size:clamp(3rem,8vw,6.5rem); font-weight:900; line-height:0.92; letter-spacing:-0.04em; margin-bottom:28px; transition-delay:0.1s;">
                Reserve Your<br>
                <span class="grad-text" style="font-family:'Playfair Display',serif; font-style:italic;">Royal Table</span><br>
                <span style="color:#3f3f46;">Instantly</span>
            </h1>

            {{-- Subheading --}}
            <p class="scroll-fade" style="font-size:clamp(1rem,2vw,1.2rem); color:#a1a1aa; max-width:620px; margin:0 auto 40px; line-height:1.75; font-weight:400; transition-delay:0.2s;">
                Discover India's finest restaurants, pre-pay securely via Stripe, and get digital invoices — all managed in one unified workspace.
            </p>

            {{-- CTA buttons --}}
            <div class="scroll-fade" style="display:flex; flex-wrap:wrap; gap:14px; justify-content:center; margin-bottom:64px; transition-delay:0.3s;">
                <a href="{{ route('register') }}" class="btn-primary" style="font-size:15px; padding:16px 36px;">
                    <i class="fa-solid fa-calendar-check"></i>
                    Start Dining Free
                </a>
                <a href="#restaurants" class="btn-outline" style="font-size:15px; padding:16px 36px;">
                    <i class="fa-solid fa-magnifying-glass" style="color:#f59e0b;"></i>
                    Browse Restaurants
                </a>
            </div>

            {{-- Stats --}}
            <div class="scroll-fade" style="display:grid; grid-template-columns:repeat(3,1fr); gap:0; max-width:480px; margin:0 auto; transition-delay:0.4s; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:18px; padding:24px;">
                <div style="text-align:center; padding:0 16px;">
                    <div class="stat-val">10+</div>
                    <div class="stat-label">Fine Bistros</div>
                </div>
                <div style="text-align:center; padding:0 16px; border-left:1px solid rgba(255,255,255,0.06); border-right:1px solid rgba(255,255,255,0.06);">
                    <div class="stat-val">55k+</div>
                    <div class="stat-label">Reservations</div>
                </div>
                <div style="text-align:center; padding:0 16px;">
                    <div class="stat-val">4.8<i class="fa-solid fa-star" style="font-size:0.6em; margin-left:2px;"></i></div>
                    <div class="stat-label">Avg Rating</div>
                </div>
            </div>
        </div>

        {{-- Hero mock dashboard --}}
        <div class="scroll-fade" style="position:relative; z-index:1; max-width:960px; margin:64px auto 0; padding:0 24px; width:100%; transition-delay:0.5s;">
            <div style="background:rgba(9,9,11,0.9); border:1px solid rgba(255,255,255,0.07); border-radius:24px; overflow:hidden; box-shadow: 0 40px 80px -20px rgba(0,0,0,0.7), 0 0 0 1px rgba(245,158,11,0.06);">
                {{-- Browser bar --}}
                <div style="background:rgba(255,255,255,0.02); border-bottom:1px solid rgba(255,255,255,0.05); padding:12px 18px; display:flex; align-items:center; gap:10px;">
                    <div style="display:flex; gap:6px;">
                        <span style="width:12px;height:12px;border-radius:50%;background:rgba(239,68,68,0.6);"></span>
                        <span style="width:12px;height:12px;border-radius:50%;background:rgba(234,179,8,0.6);"></span>
                        <span style="width:12px;height:12px;border-radius:50%;background:rgba(34,197,94,0.6);"></span>
                    </div>
                    <div style="flex:1; background:rgba(255,255,255,0.04); border-radius:8px; padding:5px 14px;">
                        <span style="font-size:12px; font-family:monospace; color:#52525b;">zaykadining.com/dashboard</span>
                    </div>
                    <span class="lp-badge" style="font-size:9px; padding:3px 10px;">LIVE</span>
                </div>

                {{-- Dashboard preview --}}
                <div style="padding:20px; display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px;">
                    @php $previewResto = \App\Models\Restaurant::take(3)->get(); @endphp

                    {{-- KPI cards --}}
                    <div class="mock-card" style="grid-column:1; display:flex; flex-direction:column; gap:4px;">
                        <div style="font-size:10px; color:#52525b; font-weight:700; text-transform:uppercase; letter-spacing:0.08em;">Today's Revenue</div>
                        <div style="font-size:24px; font-weight:900; color:#f59e0b; font-family:'Syne',sans-serif; margin-top:4px;">₹45,850</div>
                        <div style="font-size:11px; color:#4ade80; margin-top:2px;"><i class="fa-solid fa-arrow-trend-up"></i> +12% yesterday</div>
                    </div>

                    <div class="mock-card" style="grid-column:2; display:flex; flex-direction:column; gap:4px;">
                        <div style="font-size:10px; color:#52525b; font-weight:700; text-transform:uppercase; letter-spacing:0.08em;">Bookings Today</div>
                        <div style="font-size:24px; font-weight:900; color:#38bdf8; font-family:'Syne',sans-serif; margin-top:4px;">24</div>
                        <div style="font-size:11px; color:#a1a1aa; margin-top:2px;">3 pending · 21 confirmed</div>
                    </div>

                    <div class="mock-card" style="grid-column:3; display:flex; flex-direction:column; gap:4px;">
                        <div style="font-size:10px; color:#52525b; font-weight:700; text-transform:uppercase; letter-spacing:0.08em;">Staff On Duty</div>
                        <div style="font-size:24px; font-weight:900; color:#a78bfa; font-family:'Syne',sans-serif; margin-top:4px;">8</div>
                        <div style="font-size:11px; color:#a1a1aa; margin-top:2px;">2 managers · 6 staff</div>
                    </div>

                    {{-- Recent bookings --}}
                    <div class="mock-card" style="grid-column:1 / span 2;">
                        <div style="font-size:10px; color:#52525b; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:12px;">Recent Reservations</div>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            @foreach($previewResto as $pr)
                            <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 10px; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.04); border-radius:10px;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="width:32px;height:32px; border-radius:8px; overflow:hidden; background:rgba(245,158,11,0.1); flex-shrink:0;">
                                        @if($pr->image_path)
                                            <img src="{{ asset('storage/'.$pr->image_path) }}" style="width:100%;height:100%;object-fit:cover;">
                                        @else
                                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#f59e0b;font-size:12px;"><i class="fa-solid fa-bowl-food"></i></div>
                                        @endif
                                    </div>
                                    <div>
                                        <div style="font-size:11px; font-weight:700; color:#d4d4d8;">{{ Str::limit($pr->name, 18) }}</div>
                                        <div style="font-size:10px; color:#52525b;">4 guests · 8:00 PM</div>
                                    </div>
                                </div>
                                <span style="font-size:9px; font-weight:800; padding:3px 8px; border-radius:999px; {{ $loop->iteration === 2 ? 'background:rgba(245,158,11,0.1); color:#f59e0b; border:1px solid rgba(245,158,11,0.2);' : 'background:rgba(74,222,128,0.1); color:#4ade80; border:1px solid rgba(74,222,128,0.2);' }}">
                                    {{ $loop->iteration === 2 ? 'PENDING' : 'CONFIRMED' }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Floor status --}}
                    <div class="mock-card" style="grid-column:3;">
                        <div style="font-size:10px; color:#52525b; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:12px;">Floor Status</div>
                        <div style="display:flex; flex-direction:column; gap:6px;">
                            @foreach(['Table A1','Suite B1','Terrace C1'] as $i => $tn)
                            <div style="display:flex; align-items:center; justify-content:space-between;">
                                <span style="font-size:10px; color:#a1a1aa;">{{ $tn }}</span>
                                <span style="font-size:9px; font-weight:700; padding:2px 7px; border-radius:999px; {{ $i===1 ? 'background:rgba(245,158,11,0.1);color:#f59e0b;border:1px solid rgba(245,158,11,0.2);' : 'background:rgba(74,222,128,0.1);color:#4ade80;border:1px solid rgba(74,222,128,0.2);' }}">
                                    {{ $i===1 ? 'BUSY' : 'FREE' }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{-- /HERO --}}

    {{-- ═══ MARQUEE TICKER ═══ --}}
    <div style="border-top:1px solid rgba(255,255,255,0.05); border-bottom:1px solid rgba(255,255,255,0.05); padding:16px 0; overflow:hidden;">
        <div class="marquee-track" style="align-items:center;">
            @php $allR = \App\Models\Restaurant::where('is_active', true)->get(); @endphp
            @foreach(array_merge($allR->all(), $allR->all()) as $mr)
                <div style="display:flex; align-items:center; gap:10px; white-space:nowrap; color:#52525b; font-size:13px; font-weight:600;">
                    <i class="fa-solid fa-utensils" style="color:#f59e0b; font-size:10px; opacity:0.6;"></i>
                    {{ $mr->name }}
                </div>
            @endforeach
        </div>
    </div>

    {{-- ═══════════════════════════ RESTAURANTS ═══════════════════════════ --}}
    <section id="restaurants" style="padding:100px 24px; position:relative; overflow:hidden;">
        <div class="lp-blob" style="width:500px; height:500px; background:rgba(245,158,11,0.05); top:50%; right:-100px;"></div>

        <div style="max-width:1100px; margin:0 auto; position:relative; z-index:1;">
            {{-- Header --}}
            <div style="text-align:center; margin-bottom:60px;" class="scroll-fade">
                <div class="lp-badge" style="margin-bottom:20px; display:inline-flex;">
                    <i class="fa-solid fa-star"></i> Curated Establishments
                </div>
                <h2 style="font-family:'Syne',sans-serif; font-size:clamp(2rem,5vw,3.5rem); font-weight:900; letter-spacing:-0.03em; margin-bottom:16px; line-height:1.1;">
                    Fine Dining <span class="grad-text">Near You</span>
                </h2>
                <p class="lp-muted" style="font-size:16px; max-width:520px; margin:0 auto 32px; line-height:1.7;">
                    Explore luxury multicuisine restaurants registered on Zayka. View menus, check table availability, and book instantly.
                </p>

                {{-- Live search --}}
                <div style="position:relative; display:inline-block;">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#52525b; font-size:13px;"></i>
                    <input
                        wire:model.live.debounce.300ms="searchQuery"
                        type="text"
                        placeholder="Search restaurants or city..."
                        class="search-input"
                    >
                </div>
            </div>

            {{-- Grid --}}
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:24px;">
                @php
                $cuisines = ['Awadhi','Mughlai','Rajasthani','South Indian','Coastal Kerala','Dum Biryani','Tandoori'];
                @endphp
                @forelse($this->restaurants as $idx => $resto)
                    <a href="{{ route('restaurant.landing', ['id' => $resto->id]) }}" class="lp-card" style="display:block; text-decoration:none; animation-delay:{{ $idx * 60 }}ms;" class="scroll-fade">
                        {{-- Image --}}
                        <div class="card-img-wrap" style="position:relative; height:220px; overflow:hidden; background:#0a0a0a;">
                            @if($resto->image_path)
                                <img src="{{ asset('storage/'.$resto->image_path) }}" alt="{{ $resto->name }}" style="width:100%; height:100%; object-fit:cover; display:block;">
                            @else
                                <div style="width:100%; height:100%; background:linear-gradient(135deg, #1a0a00, #0a0a0a); display:flex; align-items:center; justify-content:center;">
                                    <i class="fa-solid fa-bowl-food" style="font-size:3rem; color:rgba(245,158,11,0.25);"></i>
                                </div>
                            @endif
                            {{-- Gradient overlay --}}
                            <div style="position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.15) 50%, transparent 100%);"></div>
                            {{-- Badges --}}
                            <div style="position:absolute; top:14px; left:14px;">
                                <span class="cuisine-tag">{{ $cuisines[$idx % count($cuisines)] }}</span>
                            </div>
                            <div style="position:absolute; top:14px; right:14px;">
                                <span class="rating-badge"><i class="fa-solid fa-star" style="font-size:9px;"></i> {{ number_format($resto->rating, 1) }}</span>
                            </div>
                            {{-- Bottom title on image --}}
                            <div style="position:absolute; bottom:14px; left:16px; right:16px;">
                                <h3 style="font-family:'Syne',sans-serif; font-size:17px; font-weight:800; color:#fff; margin:0; line-height:1.2;">{{ $resto->name }}</h3>
                            </div>
                        </div>

                        {{-- Card body --}}
                        <div style="padding:20px 20px 22px;">
                            <div style="display:flex; align-items:flex-start; gap:8px; margin-bottom:16px;">
                                <i class="fa-solid fa-location-dot" style="color:#f59e0b; font-size:12px; margin-top:3px; flex-shrink:0;"></i>
                                <p class="lp-muted" style="font-size:13px; line-height:1.5; margin:0; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">{{ $resto->address }}</p>
                            </div>

                            <hr class="lp-divider" style="margin-bottom:16px;">

                            <div style="display:flex; align-items:center; justify-content:space-between;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <span class="lp-subtle" style="font-size:12px; display:flex; align-items:center; gap:5px;">
                                        <i class="fa-solid fa-comment-dots" style="font-size:11px;"></i> {{ $resto->reviews_count }} reviews
                                    </span>
                                    <span class="lp-subtle" style="font-size:12px; display:flex; align-items:center; gap:5px;">
                                        <i class="fa-solid fa-chair" style="font-size:11px;"></i> {{ $resto->tables_count ?? $resto->tables()->count() }} tables
                                    </span>
                                </div>
                                <span style="font-size:12px; font-weight:700; color:#f59e0b; display:flex; align-items:center; gap:5px;">
                                    View <i class="fa-solid fa-arrow-right" style="font-size:10px;"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div style="grid-column:1/-1; text-align:center; padding:80px 20px; color:#52525b;">
                        <i class="fa-solid fa-bowl-food" style="font-size:3rem; margin-bottom:16px; display:block; opacity:0.4;"></i>
                        <p style="font-size:16px;">No restaurants found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
    {{-- /RESTAURANTS --}}

    <hr class="lp-divider">

    {{-- ═══════════════════════════ FEATURES ═══════════════════════════ --}}
    <section id="features" style="padding:100px 24px; position:relative; overflow:hidden;">
        <div class="lp-blob" style="width:500px; height:500px; background:rgba(234,88,12,0.05); top:50%; left:-100px;"></div>
        <div style="max-width:1100px; margin:0 auto; position:relative; z-index:1;">
            <div style="text-align:center; margin-bottom:60px;" class="scroll-fade">
                <div class="lp-badge" style="margin-bottom:20px; display:inline-flex;">
                    <i class="fa-solid fa-shield-halved"></i> Platform Features
                </div>
                <h2 style="font-family:'Syne',sans-serif; font-size:clamp(2rem,5vw,3.5rem); font-weight:900; letter-spacing:-0.03em; margin-bottom:16px; line-height:1.1;">
                    Everything In <span class="grad-text">One Place</span>
                </h2>
                <p class="lp-muted" style="font-size:16px; max-width:520px; margin:0 auto; line-height:1.7;">
                    From real-time table booking to automated invoices — Zayka connects every part of the dining experience.
                </p>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:20px;">
                @php
                $features = [
                    ['fa-chair',          'Real-Time Table Booking',  'Reserve seats with live availability checks. No double-bookings, no manual confirmations needed.'],
                    ['fa-credit-card',    'Stripe Secure Payments',   'Pay advance deposits or full pre-payment securely. Powered by Stripe — supports test mode.'],
                    ['fa-file-invoice',   'Instant PDF Invoices',     'Automated PDF invoices dispatched to your inbox upon booking confirmation. No manual billing.'],
                    ['fa-user-clock',     'Staff Shift Tracker',      'Chefs and servers check in/out digitally. Managers get a full live duty roster.'],
                    ['fa-chart-line',     'Live Analytics Dashboard', 'Real-time revenue, bookings, and table occupancy KPIs — all in a single manager console.'],
                    ['fa-shield-halved',  'Role-Based Access Control','Separate dashboards for diners, staff, managers, and admins. Powered by Spatie Permissions.'],
                ];
                @endphp
                @foreach($features as $i => $f)
                    <div class="feat-card scroll-fade" style="transition-delay:{{ $i * 80 }}ms;">
                        <div class="icon-box">
                            <i class="fa-solid {{ $f[0] }}"></i>
                        </div>
                        <h3 style="font-family:'Syne',sans-serif; font-size:18px; font-weight:800; margin:0 0 12px; line-height:1.3; color:inherit;">{{ $f[1] }}</h3>
                        <p class="lp-muted" style="font-size:14px; line-height:1.7; margin:0;">{{ $f[2] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <hr class="lp-divider">

    {{-- ═══════════════════════════ HOW IT WORKS ═══════════════════════════ --}}
    <section id="how" style="padding:100px 24px;">
        <div style="max-width:1100px; margin:0 auto;">
            <div style="text-align:center; margin-bottom:60px;" class="scroll-fade">
                <div class="lp-badge" style="margin-bottom:20px; display:inline-flex;">
                    <i class="fa-solid fa-route"></i> 3 Simple Steps
                </div>
                <h2 style="font-family:'Syne',sans-serif; font-size:clamp(2rem,5vw,3.5rem); font-weight:900; letter-spacing:-0.03em; margin-bottom:16px; line-height:1.1;">
                    Book in Under <span class="grad-text">2 Minutes</span>
                </h2>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:24px;">
                @php
                $steps = [
                    ['01', 'fa-magnifying-glass', 'Discover', 'Browse our curated list of premium Indian restaurants. Filter by cuisine type, rating, and city location.'],
                    ['02', 'fa-calendar-check',   'Reserve',  'Pick your date, time, and preferred table. Pay a secure advance deposit via Stripe — no hidden charges ever.'],
                    ['03', 'fa-bowl-food',         'Dine',     'Arrive and enjoy! Your digital invoice is already in your inbox before you even sit down.'],
                ];
                @endphp
                @foreach($steps as $i => $step)
                    <div class="feat-card scroll-fade" style="text-align:center; transition-delay:{{ $i * 120 }}ms;">
                        <div class="step-circle" style="margin:0 auto 24px;">{{ $step[0] }}</div>
                        <div class="icon-box" style="margin:0 auto 24px;">
                            <i class="fa-solid {{ $step[1] }}"></i>
                        </div>
                        <h3 style="font-family:'Syne',sans-serif; font-size:20px; font-weight:800; margin:0 0 14px; color:inherit;">{{ $step[2] }}</h3>
                        <p class="lp-muted" style="font-size:14px; line-height:1.7; margin:0;">{{ $step[3] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <hr class="lp-divider">

    {{-- ═══════════════════════════ ROLE PORTALS ═══════════════════════════ --}}
    <section style="padding:100px 24px; position:relative; overflow:hidden;">
        <div class="lp-blob" style="width:500px; height:500px; background:rgba(245,158,11,0.05); bottom:0; right:5%;"></div>
        <div style="max-width:1100px; margin:0 auto; position:relative; z-index:1; display:grid; grid-template-columns:1fr 1fr; gap:64px; align-items:center;">
            {{-- Left --}}
            <div class="scroll-fade">
                <div class="lp-badge" style="margin-bottom:24px; display:inline-flex;">
                    <i class="fa-solid fa-layer-group"></i> Unified Workspace
                </div>
                <h2 style="font-family:'Syne',sans-serif; font-size:clamp(2rem,4vw,3rem); font-weight:900; letter-spacing:-0.03em; margin-bottom:20px; line-height:1.1;">
                    One Platform,<br><span class="grad-text">Four Dashboards</span>
                </h2>
                <p class="lp-muted" style="font-size:16px; line-height:1.75; margin-bottom:36px;">
                    Every stakeholder gets a purpose-built experience tailored precisely to their workflow.
                </p>
                <div style="display:flex; flex-direction:column; gap:14px;">
                    @php
                    $portals = [
                        ['fa-user',            'Diner Portal',     'Browse, reserve tables, track bookings, and download invoices.', '#4ade80'],
                        ['fa-clock',           'Staff Workspace',  'Digital check-in/out, view today\'s guests, manage assigned tables.', '#38bdf8'],
                        ['fa-chart-bar',       'Manager Console',  'Approve bookings, manage menus, staff rosters, and revenue KPIs.', '#f59e0b'],
                        ['fa-shield-halved',   'SaaS Admin Panel', 'Full system oversight — tenants, roles, permissions, and platform audits.', '#a78bfa'],
                    ];
                    @endphp
                    @foreach($portals as $p)
                        <div class="feat-card" style="padding:18px 20px; display:flex; align-items:flex-start; gap:16px; border-radius:14px;">
                            <div style="width:40px; height:40px; border-radius:12px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <i class="fa-solid {{ $p[0] }}" style="color:{{ $p[3] }}; font-size:15px;"></i>
                            </div>
                            <div>
                                <div style="font-size:14px; font-weight:700; margin-bottom:4px; color:inherit;">{{ $p[1] }}</div>
                                <div class="lp-muted" style="font-size:13px; line-height:1.6;">{{ $p[2] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div style="margin-top:36px;">
                    <a href="{{ route('register') }}" class="btn-primary" style="font-size:14px; padding:14px 30px;">
                        <i class="fa-solid fa-user-plus"></i> Create Your Account
                    </a>
                </div>
            </div>

            {{-- Right — Manager console mockup --}}
            <div class="scroll-fade" style="transition-delay:0.2s;">
                <div style="background:rgba(9,9,11,0.95); border:1px solid rgba(255,255,255,0.07); border-radius:24px; overflow:hidden; box-shadow: 0 0 60px -20px rgba(245,158,11,0.15);">
                    <div style="background:rgba(255,255,255,0.02); border-bottom:1px solid rgba(255,255,255,0.05); padding:12px 18px; display:flex; align-items:center; gap:10px;">
                        <div style="display:flex; gap:6px;">
                            <span style="width:10px;height:10px;border-radius:50%;background:rgba(239,68,68,0.6);"></span>
                            <span style="width:10px;height:10px;border-radius:50%;background:rgba(234,179,8,0.6);"></span>
                            <span style="width:10px;height:10px;border-radius:50%;background:rgba(34,197,94,0.6);"></span>
                        </div>
                        <span style="font-size:11px; font-family:monospace; color:#52525b;">Manager Console</span>
                        <span class="lp-badge" style="margin-left:auto; font-size:9px; padding:3px 10px;">LIVE</span>
                    </div>
                    <div style="padding:20px; display:flex; flex-direction:column; gap:14px;">
                        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:10px;">
                            @foreach([['₹38k','Revenue','#f59e0b'],['24','Bookings','#4ade80'],['8','On Duty','#38bdf8']] as $kpi)
                            <div class="mock-card" style="text-align:center;">
                                <div style="font-size:20px; font-weight:900; color:{{ $kpi[2] }}; font-family:'Syne',sans-serif; line-height:1.2;">{{ $kpi[0] }}</div>
                                <div style="font-size:10px; color:#52525b; font-weight:600; margin-top:4px;">{{ $kpi[1] }}</div>
                            </div>
                            @endforeach
                        </div>

                        <div class="mock-card">
                            <div style="font-size:10px; color:#52525b; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:12px;">Live Floor Map</div>
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                @foreach([['Royal Table A1','FREE','#4ade80'],['Maharaja Suite B1','OCCUPIED','#f59e0b'],['Haveli Terrace C1','FREE','#4ade80']] as $tbl)
                                <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 10px; background:rgba(255,255,255,0.02); border-radius:10px; border:1px solid rgba(255,255,255,0.03);">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <i class="fa-solid fa-chair" style="color:{{ $tbl[2] }}; font-size:11px;"></i>
                                        <span style="font-size:11px; color:#a1a1aa;">{{ $tbl[0] }}</span>
                                    </div>
                                    <span style="font-size:9px; font-weight:800; color:{{ $tbl[2] }}; background:rgba(255,255,255,0.03); padding:3px 8px; border-radius:999px;">{{ $tbl[1] }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div style="background:rgba(249,115,22,0.06); border:1px solid rgba(249,115,22,0.2); border-radius:12px; padding:12px 16px; display:flex; align-items:center; gap:10px;">
                            <span style="width:8px; height:8px; border-radius:50%; background:#f97316; animation:pulse 2s infinite; flex-shrink:0;"></span>
                            <span style="font-size:12px; color:#fdba74; font-weight:600;">Head Chef Devendra · On Shift 4h 20m</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <hr class="lp-divider">

    {{-- ═══════════════════════════ PRICING ═══════════════════════════ --}}
    <section id="pricing" style="padding:100px 24px; position:relative; overflow:hidden;">
        <div class="lp-blob" style="width:600px; height:600px; background:rgba(245,158,11,0.05); top:50%; left:50%; transform:translate(-50%,-50%);"></div>
        <div style="max-width:1100px; margin:0 auto; position:relative; z-index:1;">
            <div style="text-align:center; margin-bottom:60px;" class="scroll-fade">
                <div class="lp-badge" style="margin-bottom:20px; display:inline-flex;">
                    <i class="fa-solid fa-tag"></i> Transparent Pricing
                </div>
                <h2 style="font-family:'Syne',sans-serif; font-size:clamp(2rem,5vw,3.5rem); font-weight:900; letter-spacing:-0.03em; margin-bottom:16px; line-height:1.1;">
                    Simple <span class="grad-text">Plans</span>
                </h2>
                <p class="lp-muted" style="font-size:16px; max-width:480px; margin:0 auto; line-height:1.7;">
                    Scale your reservation capacity as you grow. No hidden fees, no surprises.
                </p>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:24px;">
                @php
                $plans = [
                    ['Bistro Starter', '₹1,999', false, ['1 Restaurant Profile', '8 Seating Tables', 'Up to 5 Staff Members', 'Standard Invoices', null]],
                    ['Imperial Suite', '₹4,999', true,  ['3 Restaurant Branches', 'Unlimited Tables', '25 Staff Members', 'Custom PDF Invoices', 'Priority Support']],
                    ['Maharaja Enterprise', '₹9,999', false, ['Unlimited Branches', 'Unlimited Tables & Staff', 'API Access & Export', 'White-Label Option', 'Dedicated Account Manager']],
                ];
                @endphp
                @foreach($plans as $pi => $plan)
                    <div class="price-card {{ $plan[2] ? 'featured' : '' }} scroll-fade" style="position:relative; transition-delay:{{ $pi * 100 }}ms;">
                        @if($plan[2])
                            <div style="position:absolute; top:-14px; left:50%; transform:translateX(-50%); background:linear-gradient(135deg,#f59e0b,#ea580c); color:#000; font-size:10px; font-weight:900; letter-spacing:0.1em; text-transform:uppercase; padding:6px 18px; border-radius:999px; white-space:nowrap;">
                                Most Popular
                            </div>
                        @endif
                        <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; color:{{ $plan[2] ? '#f59e0b' : '#71717a' }}; margin-bottom:16px;">{{ $plan[0] }}</div>
                        <div style="font-family:'Syne',sans-serif; font-size:clamp(2.5rem,5vw,3.5rem); font-weight:900; line-height:1; margin-bottom:4px;">{{ $plan[1] }}</div>
                        <div class="lp-muted" style="font-size:13px; margin-bottom:28px;">per month · billed annually</div>
                        <hr class="lp-divider" style="margin-bottom:24px;">
                        <ul style="list-style:none; padding:0; margin:0 0 32px; display:flex; flex-direction:column; gap:14px;">
                            @foreach($plan[3] as $feat)
                                @if($feat)
                                    <li style="display:flex; align-items:center; gap:10px; font-size:14px; line-height:1.4;">
                                        <i class="fa-solid fa-circle-check" style="color:#f59e0b; font-size:13px; flex-shrink:0;"></i>
                                        <span>{{ $feat }}</span>
                                    </li>
                                @else
                                    <li style="display:flex; align-items:center; gap:10px; font-size:14px; line-height:1.4; opacity:0.3; text-decoration:line-through;">
                                        <i class="fa-solid fa-circle-xmark" style="font-size:13px; flex-shrink:0; color:#71717a;"></i>
                                        <span>Advanced Analytics</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                        @if($plan[2])
                            <a href="{{ route('register') }}" class="btn-primary" style="width:100%; justify-content:center; font-size:14px; padding:14px;">
                                Get Started <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-outline" style="width:100%; justify-content:center; font-size:14px; padding:14px;">
                                Select Plan
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <hr class="lp-divider">

    {{-- ═══════════════════════════ TESTIMONIALS ═══════════════════════════ --}}
    <section style="padding:100px 24px;">
        <div style="max-width:1100px; margin:0 auto;">
            <div style="text-align:center; margin-bottom:60px;" class="scroll-fade">
                <div class="lp-badge" style="margin-bottom:20px; display:inline-flex;">
                    <i class="fa-solid fa-quote-left"></i> Testimonials
                </div>
                <h2 style="font-family:'Syne',sans-serif; font-size:clamp(2rem,5vw,3.5rem); font-weight:900; letter-spacing:-0.03em; margin-bottom:16px; line-height:1.1;">
                    Loved by <span class="grad-text">Restaurant Owners</span>
                </h2>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:24px;">
                @php
                $quotes = [
                    ['Vikram Singh', 'GM · Masala Heritage, Jaipur', 'VS', '#f97316', 'The staff attendance tracking and automated invoices are game-changers. No more table overlaps or manual billing. Zayka completely simplified my manager duties.'],
                    ['Ananya Kapoor', 'Director · The Royal Tandoor, Mumbai', 'AK', '#a78bfa', 'Diners love the Stripe payment option. They pay their advance deposit, get an instant invoice, and our hostesses are notified on the staff dashboard immediately.'],
                    ['Rajan Mehta', 'Owner · Zayka Haveli, Delhi', 'RM', '#4ade80', 'The multi-restaurant isolation is incredible. My three branches operate completely independently yet I see a unified revenue dashboard in the admin panel.'],
                ];
                @endphp
                @foreach($quotes as $qi => $q)
                    <div class="quote-card scroll-fade" style="transition-delay:{{ $qi * 100 }}ms;">
                        <div style="position:relative; z-index:1;">
                            <div class="stars" style="margin-bottom:18px;">
                                @for($s=0;$s<5;$s++)<i class="fa-solid fa-star"></i>@endfor
                            </div>
                            <p style="font-size:14px; line-height:1.8; margin:0 0 24px; color:inherit;">{{ $q[4] }}</p>
                            <div style="display:flex; align-items:center; gap:12px; padding-top:18px; border-top:1px solid rgba(255,255,255,0.05);">
                                <div style="width:40px; height:40px; border-radius:50%; background:{{ $q[3] }}; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:13px; color:#000; flex-shrink:0; font-family:'Syne',sans-serif;">{{ $q[2] }}</div>
                                <div>
                                    <div style="font-size:14px; font-weight:700;">{{ $q[0] }}</div>
                                    <div class="lp-muted" style="font-size:12px;">{{ $q[1] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════ CTA BAND ═══════════════════════════ --}}
    <section style="padding:80px 24px 100px;">
        <div style="max-width:860px; margin:0 auto;" class="scroll-fade">
            <div class="cta-section" style="padding:64px 48px; text-align:center; position:relative; overflow:hidden;">
                <div class="lp-blob" style="width:400px; height:400px; background:rgba(245,158,11,0.08); top:50%; left:50%; transform:translate(-50%,-50%);"></div>
                <div style="position:relative; z-index:1;">
                    <div class="lp-badge" style="margin-bottom:24px; display:inline-flex;">
                        <i class="fa-solid fa-rocket"></i> Start Today
                    </div>
                    <h2 style="font-family:'Syne',sans-serif; font-size:clamp(2rem,5vw,3.2rem); font-weight:900; letter-spacing:-0.03em; margin-bottom:20px; line-height:1.1;">
                        Ready to Elevate Your<br><span class="grad-text">Dining Experience?</span>
                    </h2>
                    <p class="lp-muted" style="font-size:16px; max-width:480px; margin:0 auto 36px; line-height:1.75;">
                        Join hundreds of Indian fine dining establishments using Zayka Dining to manage reservations and delight guests.
                    </p>
                    <div style="display:flex; flex-wrap:wrap; gap:14px; justify-content:center;">
                        <a href="{{ route('register') }}" class="btn-primary" style="font-size:15px; padding:16px 36px;">
                            <i class="fa-solid fa-utensils"></i> Onboard Your Restaurant
                        </a>
                        <a href="#restaurants" class="btn-outline" style="font-size:15px; padding:16px 36px;">
                            <i class="fa-solid fa-magnifying-glass" style="color:#f59e0b;"></i> Explore Restaurants
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════ FOOTER ═══════════════════════════ --}}
    <div class="lp-footer">
        <div style="max-width:1100px; margin:0 auto; padding:60px 24px 32px;">
            <div style="display:grid; grid-template-columns:2fr 1fr 1fr; gap:48px; padding-bottom:48px; border-bottom:1px solid rgba(255,255,255,0.05);">
                {{-- Brand col --}}
                <div>
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
                        <div style="width:36px; height:36px; border-radius:12px; background:linear-gradient(135deg,#f59e0b,#ea580c); display:flex; align-items:center; justify-content:center;">
                            <i class="fa-solid fa-bowl-rice" style="color:#000; font-size:13px;"></i>
                        </div>
                        <span style="font-family:'Syne',sans-serif; font-weight:800; font-size:16px;">Zayka <em style="font-family:'Playfair Display',serif; color:#f59e0b; font-style:italic;">Dining</em></span>
                    </div>
                    <p class="lp-muted" style="font-size:13px; line-height:1.75; max-width:260px; margin-bottom:20px;">
                        India's premium multi-restaurant reservation SaaS. Real-time tables, automated invoices, and shift management.
                    </p>
                    <div style="display:flex; gap:10px;">
                        @foreach(['fa-instagram','fa-twitter','fa-linkedin-in'] as $soc)
                        <a href="#" style="width:36px; height:36px; border-radius:10px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; justify-content:center; color:#52525b; transition:color 0.2s;" onmouseover="this.style.color='#f59e0b'" onmouseout="this.style.color='#52525b'">
                            <i class="fa-brands {{ $soc }}" style="font-size:13px;"></i>
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- Product --}}
                <div>
                    <div style="font-size:11px; font-weight:800; letter-spacing:0.1em; text-transform:uppercase; color:#52525b; margin-bottom:18px;">Product</div>
                    <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:12px;">
                        @foreach(['Restaurants:#restaurants','Features:#features','Pricing:#pricing','Blogs:'.route('blogs'),'About Us:'.route('about'),'Contact:'.route('contact')] as $lnk)
                            @php [$lt, $lh] = explode(':', $lnk, 2); @endphp
                            <li><a href="{{ $lh }}" class="lp-muted" style="font-size:13px; text-decoration:none; transition:color 0.2s;" onmouseover="this.style.color='#f59e0b'" onmouseout="this.style.color=''">{{ $lt }}</a></li>
                        @endforeach
                    </ul>
                </div>

                {{-- Contact --}}
                <div>
                    <div style="font-size:11px; font-weight:800; letter-spacing:0.1em; text-transform:uppercase; color:#52525b; margin-bottom:18px;">Contact</div>
                    <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:14px;">
                        <li style="display:flex; align-items:flex-start; gap:10px; font-size:13px;" class="lp-muted">
                            <i class="fa-solid fa-envelope" style="color:#f59e0b; opacity:0.7; margin-top:2px; flex-shrink:0;"></i>
                            support@zaykadining.com
                        </li>
                        <li style="display:flex; align-items:flex-start; gap:10px; font-size:13px;" class="lp-muted">
                            <i class="fa-solid fa-phone" style="color:#f59e0b; opacity:0.7; margin-top:2px; flex-shrink:0;"></i>
                            +91 11-45000000
                        </li>
                        <li style="display:flex; align-items:flex-start; gap:10px; font-size:13px;" class="lp-muted">
                            <i class="fa-solid fa-location-dot" style="color:#f59e0b; opacity:0.7; margin-top:2px; flex-shrink:0;"></i>
                            Connaught Place, New Delhi
                        </li>
                    </ul>
                </div>
            </div>
            <div style="padding-top:28px; display:flex; flex-wrap:wrap; gap:16px; align-items:center; justify-content:space-between;">
                <p class="lp-muted" style="font-size:12px; margin:0;">&copy; {{ now()->year }} Zayka Dining · All rights reserved.</p>
                <div style="display:flex; gap:20px;">
                    <a href="#" class="lp-muted" style="font-size:12px; text-decoration:none;">Privacy Policy</a>
                    <a href="#" class="lp-muted" style="font-size:12px; text-decoration:none;">Terms of Service</a>
                </div>
            </div>
        </div>
    </div>

</div>
{{-- /lp-root --}}

{{-- ═══════════════════════════ SCRIPTS ═══════════════════════════ --}}
<script>
// ── Apply saved theme on load ──
(function(){
    const t = localStorage.getItem('zaykaTheme') || 'dark';
    const root = document.getElementById('lp-root');
    if (root && t === 'light') root.classList.add('light');
})();

// ── Intersection Observer scroll fade animations ──
const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.classList.add('in');
            observer.unobserve(e.target);
        }
    });
}, { threshold: 0.08 });
document.querySelectorAll('.scroll-fade').forEach(el => {
    observer.observe(el);
    // Trigger immediately if already in view
    const r = el.getBoundingClientRect();
    if (r.top < window.innerHeight && r.bottom > 0) el.classList.add('in');
});
</script>

</div>
{{-- /x-data wrapper --}}
