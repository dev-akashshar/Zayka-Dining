<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Zayka Dining — Premium Indian Restaurant Reservations</title>
<meta name="description" content="Book tables at India's finest restaurants. Real-time availability, Stripe payments, and instant invoices.">
<link rel="icon" href="/favicon.ico" sizes="any">

{{-- Fonts --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800;900&family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
/* ─────────────────────────────────────────────────────
   RESET & BASE
───────────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
a { text-decoration: none; color: inherit; }
img { display: block; max-width: 100%; }

:root {
    --bg:        #09090b;
    --bg2:       #111113;
    --bg3:       #18181b;
    --border:    rgba(255,255,255,0.07);
    --text:      #f4f4f5;
    --muted:     #a1a1aa;
    --subtle:    #52525b;
    --amber:     #f59e0b;
    --orange:    #ea580c;
    --radius-sm: 12px;
    --radius-md: 18px;
    --radius-lg: 24px;
    --font-h:    'Syne', sans-serif;
    --font-b:    'Inter', sans-serif;
    --font-s:    'Playfair Display', serif;
}

/* Light mode vars */
.light-mode {
    --bg:     #f8fafc;
    --bg2:    #ffffff;
    --bg3:    #f1f5f9;
    --border: rgba(0,0,0,0.08);
    --text:   #09090b;
    --muted:  #52525b;
    --subtle: #a1a1aa;
}

body {
    font-family: var(--font-b);
    background: var(--bg);
    color: var(--text);
    line-height: 1.5;
    overflow-x: hidden;
    -webkit-font-smoothing: antialiased;
    transition: background 0.3s ease, color 0.3s ease;
    min-height: 100vh;
}
h1,h2,h3,h4,h5 { font-family: var(--font-h); line-height: 1.1; }

::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-track { background: var(--bg); }
::-webkit-scrollbar-thumb { background: var(--bg3); border-radius: 4px; }

/* ─────────────────────────────────────────────────────
   GRADIENTS & EFFECTS
───────────────────────────────────────────────────── */
.grad-text {
    background: linear-gradient(135deg, #f59e0b 0%, #ef4444 45%, #f59e0b 90%);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: shimmer 5s linear infinite;
}
@keyframes shimmer { to { background-position: 200% center; } }

.blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(110px);
    pointer-events: none;
    z-index: 0;
}

/* ─────────────────────────────────────────────────────
   NAVBAR
───────────────────────────────────────────────────── */
.navbar {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 999;
    padding: 14px 20px;
}
.nav-inner {
    max-width: 1120px;
    margin: 0 auto;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(9,9,11,0.82);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: var(--radius-md);
    padding: 0 20px;
    transition: background 0.3s;
}
.light-mode .nav-inner {
    background: rgba(248,250,252,0.88);
    border-color: rgba(0,0,0,0.08);
}
.logo {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}
.logo-icon {
    width: 36px; height: 36px;
    border-radius: 11px;
    background: linear-gradient(135deg, #f59e0b, #ea580c);
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 16px rgba(245,158,11,0.4);
    flex-shrink: 0;
}
.logo-icon i { color: #000; font-size: 14px; }
.logo-text {
    font-family: var(--font-h);
    font-weight: 800;
    font-size: 17px;
    letter-spacing: -0.02em;
    color: var(--text);
}
.logo-text em { font-family: var(--font-s); font-style: italic; color: var(--amber); }

.nav-links {
    display: flex;
    align-items: center;
    gap: 2px;
    list-style: none;
}
.nav-links a {
    font-size: 13.5px;
    font-weight: 500;
    color: var(--muted);
    padding: 7px 13px;
    border-radius: 10px;
    transition: color 0.2s, background 0.2s;
}
.nav-links a:hover { color: var(--text); background: rgba(255,255,255,0.05); }
.light-mode .nav-links a:hover { background: rgba(0,0,0,0.05); }

.nav-actions { display: flex; align-items: center; gap: 8px; }

.btn-theme {
    width: 36px; height: 36px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: rgba(255,255,255,0.04);
    color: var(--amber);
    font-size: 13px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.2s;
}
.btn-theme:hover { background: rgba(245,158,11,0.1); border-color: rgba(245,158,11,0.3); }

.btn-primary {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 20px;
    border-radius: var(--radius-sm);
    background: linear-gradient(135deg, #f59e0b, #ea580c);
    color: #000;
    font-weight: 700;
    font-size: 13.5px;
    font-family: var(--font-b);
    border: none;
    cursor: pointer;
    transition: all 0.25s;
    box-shadow: 0 4px 16px rgba(245,158,11,0.3);
    text-decoration: none;
    white-space: nowrap;
}
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(245,158,11,0.45); }

.btn-ghost {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 20px;
    border-radius: var(--radius-sm);
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text);
    font-weight: 600;
    font-size: 13.5px;
    font-family: var(--font-b);
    cursor: pointer;
    transition: all 0.25s;
    text-decoration: none;
    white-space: nowrap;
}
.btn-ghost:hover { background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.15); transform: translateY(-2px); }
.btn-signin { font-size: 13.5px; font-weight: 600; color: var(--muted); transition: color 0.2s; }
.btn-signin:hover { color: var(--text); }

.hamburger {
    display: none;
    width: 36px; height: 36px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: rgba(255,255,255,0.04);
    color: var(--muted);
    font-size: 14px;
    cursor: pointer;
    align-items: center;
    justify-content: center;
}

/* Mobile nav */
.mob-menu {
    display: none;
    max-width: 1120px;
    margin: 8px auto 0;
    background: rgba(9,9,11,0.95);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 16px 20px;
}
.light-mode .mob-menu { background: rgba(248,250,252,0.97); }
.mob-menu.open { display: block; }
.mob-menu a { display: block; padding: 10px 12px; color: var(--muted); font-size: 14px; border-radius: 10px; transition: all 0.2s; }
.mob-menu a:hover { color: var(--text); background: rgba(255,255,255,0.05); }
.mob-menu-actions { display: flex; gap: 10px; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border); }
.mob-menu-actions a { flex: 1; text-align: center; }

/* ─────────────────────────────────────────────────────
   SECTION BASE
───────────────────────────────────────────────────── */
.section { padding: 96px 24px; position: relative; overflow: hidden; }
.section-inner { max-width: 1120px; margin: 0 auto; position: relative; z-index: 1; }
.section-border { border-top: 1px solid var(--border); }

.section-header { text-align: center; margin-bottom: 56px; }
.eyebrow {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(245,158,11,0.1);
    border: 1px solid rgba(245,158,11,0.2);
    color: var(--amber);
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    padding: 6px 16px;
    border-radius: 999px;
    margin-bottom: 20px;
}
.section-title {
    font-size: clamp(2rem, 4.5vw, 3.2rem);
    font-weight: 900;
    letter-spacing: -0.03em;
    margin-bottom: 16px;
    line-height: 1.05;
}
.section-sub {
    font-size: 15.5px;
    color: var(--muted);
    line-height: 1.75;
    max-width: 520px;
    margin: 0 auto;
}

/* ─────────────────────────────────────────────────────
   HERO
───────────────────────────────────────────────────── */
.hero {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 140px 24px 80px;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.hero-headline {
    font-size: clamp(3.2rem, 9vw, 6.5rem);
    font-weight: 900;
    letter-spacing: -0.04em;
    line-height: 0.92;
    margin-bottom: 28px;
}
.hero-headline .dim { color: #3f3f46; }
.hero-sub {
    font-size: clamp(1rem, 2.5vw, 1.2rem);
    color: var(--muted);
    max-width: 580px;
    margin: 0 auto 44px;
    line-height: 1.75;
    font-weight: 400;
}
.hero-ctas { display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; margin-bottom: 64px; }
.hero-ctas .btn-primary { font-size: 15px; padding: 14px 32px; }
.hero-ctas .btn-ghost { font-size: 15px; padding: 14px 32px; }
.hero-ctas .btn-ghost .icon-amber { color: var(--amber); }

/* Stats strip */
.stats-strip {
    display: flex;
    align-items: stretch;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 0;
    overflow: hidden;
    margin-bottom: 0;
    max-width: 440px;
}
.light-mode .stats-strip { background: rgba(0,0,0,0.03); }
.stat-item { flex: 1; text-align: center; padding: 22px 16px; }
.stat-item + .stat-item { border-left: 1px solid var(--border); }
.stat-val {
    font-family: var(--font-h);
    font-size: 2rem;
    font-weight: 900;
    background: linear-gradient(135deg, #fbbf24, #f97316);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1;
    margin-bottom: 6px;
}
.stat-lbl { font-size: 10.5px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--subtle); }

/* Hero dashboard preview */
.hero-dashboard {
    width: 100%;
    max-width: 980px;
    margin: 56px auto 0;
    background: rgba(9,9,11,0.92);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: 0 40px 80px -20px rgba(0,0,0,0.7), 0 0 0 1px rgba(245,158,11,0.05);
}
.light-mode .hero-dashboard { background: #fff; border-color: rgba(0,0,0,0.1); }
.db-bar {
    background: rgba(255,255,255,0.02);
    border-bottom: 1px solid rgba(255,255,255,0.05);
    padding: 12px 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.light-mode .db-bar { background: rgba(0,0,0,0.02); border-bottom-color: rgba(0,0,0,0.05); }
.db-dots { display: flex; gap: 6px; }
.db-dots span { width: 11px; height: 11px; border-radius: 50%; }
.dot-r { background: rgba(239,68,68,0.7); }
.dot-y { background: rgba(234,179,8,0.7); }
.dot-g { background: rgba(34,197,94,0.7); }
.db-url {
    flex: 1;
    background: rgba(255,255,255,0.04);
    border-radius: 8px;
    padding: 5px 14px;
    font-family: monospace;
    font-size: 12px;
    color: var(--subtle);
}
.light-mode .db-url { background: rgba(0,0,0,0.05); }
.db-live {
    font-size: 9px; font-weight: 800;
    letter-spacing: 0.12em; text-transform: uppercase;
    background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.25);
    color: var(--amber); padding: 4px 12px; border-radius: 999px;
}
.db-body { padding: 20px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }
.db-card {
    background: rgba(255,255,255,0.025);
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 14px;
    padding: 16px;
}
.light-mode .db-card { background: rgba(0,0,0,0.025); border-color: rgba(0,0,0,0.05); }
.db-label { font-size: 10px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--subtle); margin-bottom: 10px; }
.db-num { font-family: var(--font-h); font-size: 22px; font-weight: 900; line-height: 1; margin-bottom: 4px; }
.db-sub { font-size: 11px; color: var(--subtle); }
.col-amber { color: #f59e0b; }
.col-sky   { color: #38bdf8; }
.col-violet{ color: #a78bfa; }
.col-green { color: #4ade80; }
.col-orange{ color: #fb923c; }
.db-booking-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 10px;
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.04);
    border-radius: 10px;
    margin-bottom: 6px;
}
.light-mode .db-booking-row { background: rgba(0,0,0,0.025); border-color: rgba(0,0,0,0.05); }
.db-booking-row:last-child { margin-bottom: 0; }
.db-thumb { width: 30px; height: 30px; border-radius: 8px; object-fit: cover; overflow: hidden; flex-shrink: 0; background: rgba(245,158,11,0.1); display:flex;align-items:center;justify-content:center; }
.db-thumb i { color: var(--amber); font-size: 11px; }
.db-rname { font-size: 11px; font-weight: 700; color: #d4d4d8; }
.db-rsub  { font-size: 10px; color: var(--subtle); }
.status-pill {
    font-size: 9px; font-weight: 800;
    letter-spacing: 0.08em; text-transform: uppercase;
    padding: 3px 9px; border-radius: 999px;
}
.pill-green { background: rgba(74,222,128,0.1); color: #4ade80; border: 1px solid rgba(74,222,128,0.2); }
.pill-amber { background: rgba(245,158,11,0.1); color: #f59e0b; border: 1px solid rgba(245,158,11,0.2); }
.pill-red   { background: rgba(239,68,68,0.1);  color: #f87171; border: 1px solid rgba(239,68,68,0.2);  }

/* ─────────────────────────────────────────────────────
   MARQUEE
───────────────────────────────────────────────────── */
.marquee-wrap { border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); padding: 14px 0; overflow: hidden; }
.marquee-track { display: flex; gap: 48px; animation: marquee 30s linear infinite; width: max-content; }
.marquee-item { display: flex; align-items: center; gap: 10px; white-space: nowrap; color: var(--subtle); font-size: 13px; font-weight: 600; }
.marquee-item i { color: var(--amber); font-size: 9px; opacity: 0.6; }
@keyframes marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }

/* ─────────────────────────────────────────────────────
   RESTAURANT CARDS
───────────────────────────────────────────────────── */
.search-wrap { position: relative; max-width: 380px; margin: 0 auto; }
.search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--subtle); font-size: 13px; pointer-events: none; }
.search-input {
    width: 100%;
    padding: 12px 16px 12px 40px;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    color: var(--text);
    font-size: 14px;
    font-family: var(--font-b);
    outline: none;
    transition: all 0.2s;
}
.light-mode .search-input { background: rgba(0,0,0,0.04); }
.search-input:focus { border-color: rgba(245,158,11,0.4); background: rgba(245,158,11,0.03); }
.search-input::placeholder { color: var(--subtle); }

.cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(310px, 1fr)); gap: 22px; }

.r-card {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: transform 0.35s cubic-bezier(.34,1.56,.64,1), border-color 0.3s, box-shadow 0.3s;
    cursor: pointer;
    display: block;
    color: var(--text);
    text-decoration: none;
}
.r-card:hover {
    transform: translateY(-8px);
    border-color: rgba(245,158,11,0.3);
    box-shadow: 0 28px 56px -16px rgba(0,0,0,0.55), 0 0 0 1px rgba(245,158,11,0.07);
}
.light-mode .r-card { background: #fff; }
.light-mode .r-card:hover { box-shadow: 0 28px 56px -16px rgba(0,0,0,0.1), 0 0 0 1px rgba(245,158,11,0.15); }

.r-img-wrap { position: relative; height: 200px; overflow: hidden; background: #0a0a0a; }
.r-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s cubic-bezier(.25,.46,.45,.94); }
.r-card:hover .r-img-wrap img { transform: scale(1.06); }
.r-img-gradient { position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.15) 55%, transparent 100%); }
.r-img-top { position: absolute; top: 12px; left: 12px; right: 12px; display: flex; justify-content: space-between; align-items: flex-start; }
.cuisine-badge {
    font-size: 9px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase;
    background: rgba(0,0,0,0.65); backdrop-filter: blur(8px);
    color: #fbbf24; border: 1px solid rgba(245,158,11,0.3);
    padding: 4px 10px; border-radius: 999px;
}
.rating-badge {
    display: flex; align-items: center; gap: 4px;
    background: rgba(0,0,0,0.65); backdrop-filter: blur(8px);
    border: 1px solid rgba(245,158,11,0.25);
    color: #fbbf24; font-size: 11px; font-weight: 700;
    padding: 4px 10px; border-radius: 999px;
}
.r-img-title {
    position: absolute; bottom: 12px; left: 14px; right: 14px;
    font-family: var(--font-h);
    font-size: 17px; font-weight: 800;
    color: #fff;
    line-height: 1.25;
    text-shadow: 0 2px 8px rgba(0,0,0,0.5);
}
.r-body { padding: 18px 18px 20px; }
.r-address {
    display: flex; align-items: flex-start; gap: 8px;
    color: var(--muted); font-size: 13px; line-height: 1.55;
    margin-bottom: 14px;
}
.r-address i { color: var(--amber); font-size: 11px; margin-top: 2px; flex-shrink: 0; }
.r-divider { border: none; border-top: 1px solid var(--border); margin-bottom: 14px; }
.r-footer { display: flex; align-items: center; justify-content: space-between; }
.r-meta { display: flex; align-items: center; gap: 14px; }
.r-meta span { display: flex; align-items: center; gap: 5px; font-size: 12px; color: var(--subtle); }
.r-meta i { font-size: 10px; }
.r-cta { font-size: 12px; font-weight: 700; color: var(--amber); display: flex; align-items: center; gap: 5px; transition: gap 0.2s; }
.r-card:hover .r-cta { gap: 8px; }

/* No results */
.no-results { grid-column: 1 / -1; text-align: center; padding: 80px 20px; color: var(--subtle); }
.no-results i { font-size: 3rem; margin-bottom: 16px; display: block; opacity: 0.3; }

/* ─────────────────────────────────────────────────────
   FEATURE CARDS
───────────────────────────────────────────────────── */
.feat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(310px, 1fr)); gap: 20px; }
.feat-card {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 32px 28px;
    transition: all 0.3s ease;
}
.light-mode .feat-card { background: #fff; }
.feat-card:hover { border-color: rgba(245,158,11,0.22); box-shadow: 0 16px 40px -12px rgba(0,0,0,0.4); }
.light-mode .feat-card:hover { box-shadow: 0 16px 40px -12px rgba(0,0,0,0.08); }

.feat-icon {
    width: 54px; height: 54px;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(245,158,11,0.12), rgba(234,88,12,0.06));
    border: 1px solid rgba(245,158,11,0.18);
    display: flex; align-items: center; justify-content: center;
    color: var(--amber);
    font-size: 20px;
    margin-bottom: 24px;
}
.feat-title { font-size: 17px; font-weight: 800; margin-bottom: 12px; line-height: 1.3; }
.feat-desc { font-size: 14px; color: var(--muted); line-height: 1.75; }

/* ─────────────────────────────────────────────────────
   HOW IT WORKS
───────────────────────────────────────────────────── */
.steps-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 22px; }
.step-card {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 36px 28px;
    text-align: center;
    transition: all 0.3s ease;
}
.light-mode .step-card { background: #fff; }
.step-card:hover { border-color: rgba(245,158,11,0.22); transform: translateY(-4px); }
.step-num {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(245,158,11,0.15), rgba(234,88,12,0.08));
    border: 1px solid rgba(245,158,11,0.25);
    display: flex; align-items: center; justify-content: center;
    font-family: var(--font-h); font-size: 14px; font-weight: 900;
    color: var(--amber);
    margin: 0 auto 20px;
}
.step-icon {
    width: 52px; height: 52px;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(245,158,11,0.1), rgba(234,88,12,0.05));
    border: 1px solid rgba(245,158,11,0.15);
    display: flex; align-items: center; justify-content: center;
    color: var(--amber); font-size: 20px;
    margin: 0 auto 22px;
}
.step-title { font-size: 19px; font-weight: 800; margin-bottom: 12px; }
.step-desc { font-size: 14px; color: var(--muted); line-height: 1.75; }

/* ─────────────────────────────────────────────────────
   PORTALS SECTION
───────────────────────────────────────────────────── */
.portals-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 56px; align-items: center; }
.portal-items { display: flex; flex-direction: column; gap: 14px; margin-top: 32px; }
.portal-item {
    display: flex; align-items: flex-start; gap: 14px;
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 16px 18px;
    transition: all 0.25s;
}
.light-mode .portal-item { background: #fff; }
.portal-item:hover { border-color: rgba(245,158,11,0.2); }
.portal-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
.pi-green  { background: rgba(74,222,128,0.1); border: 1px solid rgba(74,222,128,0.2); color: #4ade80; }
.pi-sky    { background: rgba(56,189,248,0.1); border: 1px solid rgba(56,189,248,0.2); color: #38bdf8; }
.pi-amber  { background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.2); color: #f59e0b; }
.pi-violet { background: rgba(167,139,250,0.1); border: 1px solid rgba(167,139,250,0.2); color: #a78bfa; }
.portal-name { font-size: 14px; font-weight: 700; margin-bottom: 4px; }
.portal-desc { font-size: 13px; color: var(--muted); line-height: 1.6; }

/* Console mockup */
.console-card {
    background: rgba(9,9,11,0.95);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: 0 0 60px -20px rgba(245,158,11,0.15);
}
.light-mode .console-card { background: #fff; border-color: rgba(0,0,0,0.1); box-shadow: 0 0 60px -20px rgba(245,158,11,0.1); }
.console-bar {
    background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.05);
    padding: 12px 18px; display: flex; align-items: center; gap: 10px;
}
.light-mode .console-bar { background: rgba(0,0,0,0.02); border-bottom-color: rgba(0,0,0,0.05); }
.console-body { padding: 20px; display: flex; flex-direction: column; gap: 14px; }
.console-kpi { display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; }
.kpi-box { background: rgba(255,255,255,0.025); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 14px 10px; text-align: center; }
.light-mode .kpi-box { background: rgba(0,0,0,0.025); border-color: rgba(0,0,0,0.05); }
.kpi-val { font-family: var(--font-h); font-size: 20px; font-weight: 900; line-height: 1; margin-bottom: 5px; }
.kpi-lbl { font-size: 10px; color: var(--subtle); font-weight: 600; }
.floor-card { background: rgba(255,255,255,0.025); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 14px 16px; }
.light-mode .floor-card { background: rgba(0,0,0,0.025); border-color: rgba(0,0,0,0.05); }
.floor-lbl { font-size: 10px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--subtle); margin-bottom: 12px; }
.floor-row { display: flex; align-items: center; justify-content: space-between; padding: 7px 8px; background: rgba(255,255,255,0.02); border-radius: 8px; margin-bottom: 6px; border: 1px solid rgba(255,255,255,0.03); }
.light-mode .floor-row { background: rgba(0,0,0,0.025); border-color: rgba(0,0,0,0.04); }
.floor-row:last-child { margin-bottom: 0; }
.floor-name { font-size: 11px; color: var(--muted); display: flex; align-items: center; gap: 7px; }
.shift-badge { background: rgba(249,115,22,0.07); border: 1px solid rgba(249,115,22,0.2); border-radius: 10px; padding: 10px 14px; display: flex; align-items: center; gap: 10px; }
.shift-dot { width: 8px; height: 8px; border-radius: 50%; background: #f97316; animation: pulse-dot 2s infinite; flex-shrink: 0; }
@keyframes pulse-dot { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:0.5; transform:scale(0.85); } }
.shift-text { font-size: 12px; color: #fdba74; font-weight: 600; }

/* ─────────────────────────────────────────────────────
   PRICING
───────────────────────────────────────────────────── */
.pricing-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px; }
.price-card {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 36px 30px;
    transition: all 0.3s;
    position: relative;
}
.light-mode .price-card { background: #fff; }
.price-card.featured {
    background: linear-gradient(160deg, rgba(245,158,11,0.06), rgba(234,88,12,0.03));
    border-color: rgba(245,158,11,0.35);
    box-shadow: 0 0 56px -20px rgba(245,158,11,0.22);
}
.popular-tag {
    position: absolute; top: -14px; left: 50%; transform: translateX(-50%);
    background: linear-gradient(135deg, #f59e0b, #ea580c);
    color: #000; font-size: 10px; font-weight: 900;
    letter-spacing: 0.1em; text-transform: uppercase;
    padding: 5px 18px; border-radius: 999px; white-space: nowrap;
}
.price-plan { font-size: 11px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 16px; }
.price-val { font-family: var(--font-h); font-size: 3rem; font-weight: 900; line-height: 1; margin-bottom: 4px; }
.price-period { font-size: 13px; color: var(--muted); margin-bottom: 28px; }
.price-divider { border: none; border-top: 1px solid var(--border); margin-bottom: 22px; }
.price-features { list-style: none; display: flex; flex-direction: column; gap: 13px; margin-bottom: 30px; }
.price-features li { display: flex; align-items: center; gap: 10px; font-size: 14px; line-height: 1.4; }
.price-features li i { font-size: 13px; flex-shrink: 0; }
.price-features .check { color: var(--amber); }
.price-features .cross { color: var(--subtle); }
.price-features .cross-txt { opacity: 0.4; text-decoration: line-through; }
.btn-plan-primary { display: block; width: 100%; text-align: center; padding: 13px; border-radius: var(--radius-sm); background: linear-gradient(135deg, #f59e0b, #ea580c); color: #000; font-weight: 700; font-size: 14px; font-family: var(--font-b); border: none; cursor: pointer; transition: all 0.25s; text-decoration: none; }
.btn-plan-primary:hover { box-shadow: 0 6px 24px rgba(245,158,11,0.4); transform: translateY(-2px); }
.btn-plan-ghost { display: block; width: 100%; text-align: center; padding: 13px; border-radius: var(--radius-sm); background: transparent; border: 1px solid var(--border); color: var(--text); font-weight: 600; font-size: 14px; font-family: var(--font-b); cursor: pointer; transition: all 0.25s; text-decoration: none; }
.btn-plan-ghost:hover { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.15); transform: translateY(-2px); }

/* ─────────────────────────────────────────────────────
   TESTIMONIALS
───────────────────────────────────────────────────── */
.quotes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 22px; }
.quote-card {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 32px 28px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s;
}
.light-mode .quote-card { background: #fff; }
.quote-card:hover { transform: translateY(-4px); border-color: rgba(245,158,11,0.15); }
.quote-card::before {
    content: '\201C';
    font-family: var(--font-s);
    font-size: 100px; line-height: 1;
    color: rgba(245,158,11,0.07);
    position: absolute; top: -5px; left: 18px;
}
.stars { color: var(--amber); font-size: 11px; margin-bottom: 16px; display: flex; gap: 3px; }
.quote-text { font-size: 14px; color: var(--muted); line-height: 1.8; margin-bottom: 22px; position: relative; z-index: 1; }
.quote-author { display: flex; align-items: center; gap: 12px; padding-top: 18px; border-top: 1px solid var(--border); }
.author-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-family: var(--font-h); font-size: 13px; font-weight: 900;
    color: #000; flex-shrink: 0;
}
.author-name { font-size: 14px; font-weight: 700; }
.author-role { font-size: 12px; color: var(--muted); margin-top: 2px; }

/* ─────────────────────────────────────────────────────
   CTA SECTION
───────────────────────────────────────────────────── */
.cta-box {
    background: linear-gradient(135deg, rgba(245,158,11,0.07), rgba(234,88,12,0.04));
    border: 1px solid rgba(245,158,11,0.15);
    border-radius: 28px;
    padding: 72px 48px;
    text-align: center;
    position: relative;
    overflow: hidden;
    max-width: 820px;
    margin: 0 auto;
}
.cta-title { font-size: clamp(1.9rem, 4.5vw, 3rem); font-weight: 900; letter-spacing: -0.03em; margin-bottom: 18px; line-height: 1.1; }
.cta-sub { font-size: 15.5px; color: var(--muted); max-width: 480px; margin: 0 auto 36px; line-height: 1.75; }
.cta-btns { display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; }
.cta-btns .btn-primary { font-size: 15px; padding: 14px 32px; }
.cta-btns .btn-ghost { font-size: 15px; padding: 14px 32px; }

/* ─────────────────────────────────────────────────────
   FOOTER
───────────────────────────────────────────────────── */
.footer { background: #060608; border-top: 1px solid var(--border); padding: 60px 24px 32px; }
.light-mode .footer { background: #f1f5f9; }
.footer-inner { max-width: 1120px; margin: 0 auto; }
.footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 48px; margin-bottom: 48px; padding-bottom: 48px; border-bottom: 1px solid var(--border); }
.footer-brand-desc { font-size: 13px; color: var(--muted); line-height: 1.75; max-width: 260px; margin: 16px 0 20px; }
.social-row { display: flex; gap: 8px; }
.social-btn { width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,0.04); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; color: var(--subtle); font-size: 13px; transition: all 0.2s; text-decoration: none; }
.social-btn:hover { color: var(--amber); border-color: rgba(245,158,11,0.3); background: rgba(245,158,11,0.06); }
.footer-heading { font-size: 10.5px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; color: var(--subtle); margin-bottom: 18px; }
.footer-links { list-style: none; display: flex; flex-direction: column; gap: 11px; }
.footer-links a { font-size: 13px; color: var(--muted); transition: color 0.2s; }
.footer-links a:hover { color: var(--amber); }
.footer-contact-items { display: flex; flex-direction: column; gap: 13px; }
.footer-contact-item { display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: var(--muted); }
.footer-contact-item i { color: var(--amber); opacity: 0.7; margin-top: 2px; flex-shrink: 0; font-size: 12px; }
.footer-bottom { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
.footer-bottom p { font-size: 12px; color: var(--subtle); }
.footer-legal { display: flex; gap: 20px; }
.footer-legal a { font-size: 12px; color: var(--subtle); transition: color 0.2s; }
.footer-legal a:hover { color: var(--muted); }

/* ─────────────────────────────────────────────────────
   SCROLL ANIMATIONS
───────────────────────────────────────────────────── */
.fade-up { opacity: 0; transform: translateY(26px); transition: opacity 0.65s ease, transform 0.65s ease; }
.fade-up.in { opacity: 1; transform: translateY(0); }

/* ─────────────────────────────────────────────────────
   RESPONSIVE
───────────────────────────────────────────────────── */
@media (max-width: 900px) {
    .portals-grid { grid-template-columns: 1fr; gap: 36px; }
    .footer-grid { grid-template-columns: 1fr; gap: 32px; }
    .db-body { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 640px) {
    .section { padding: 72px 20px; }
    .nav-links, .btn-signin, .hide-mob { display: none !important; }
    .hamburger { display: flex !important; }
    .hero-ctas .btn-primary, .hero-ctas .btn-ghost { flex: 1; justify-content: center; }
    .pricing-grid, .feat-grid { grid-template-columns: 1fr; }
    .db-body { grid-template-columns: 1fr; }
    .cta-box { padding: 48px 24px; }
    .portals-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body id="body">

@php
    $restaurants = \App\Models\Restaurant::where('is_active', true)->get();
    $isDark = true; // default dark
@endphp

{{-- ═══ NAVBAR ═══ --}}
<div class="navbar">
    <div class="nav-inner">
        <a href="/" class="logo">
            <div class="logo-icon"><i class="fa-solid fa-bowl-rice"></i></div>
            <div class="logo-text">Zayka <em>Dining</em></div>
        </a>

        <ul class="nav-links" role="list">
            <li><a href="#restaurants">Restaurants</a></li>
            <li><a href="#features">Features</a></li>
            <li><a href="#how">How It Works</a></li>
            <li><a href="#pricing">Pricing</a></li>
        </ul>

        <div class="nav-actions">
            <button class="btn-theme" id="theme-btn" onclick="toggleTheme()" title="Toggle theme">
                <i class="fa-solid fa-moon" id="theme-icon"></i>
            </button>
            @if(Route::has('login'))
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary">
                        Dashboard <i class="fa-solid fa-arrow-right fa-xs"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-signin hide-mob">Sign in</a>
                    <a href="{{ route('register') }}" class="btn-primary">
                        Get Started <i class="fa-solid fa-arrow-right fa-xs"></i>
                    </a>
                @endauth
            @endif
            <button class="hamburger" id="ham-btn" onclick="document.getElementById('mob-nav').classList.toggle('open')">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>

    <div class="mob-menu" id="mob-nav">
        <a href="#restaurants" onclick="document.getElementById('mob-nav').classList.remove('open')">Restaurants</a>
        <a href="#features" onclick="document.getElementById('mob-nav').classList.remove('open')">Features</a>
        <a href="#how" onclick="document.getElementById('mob-nav').classList.remove('open')">How It Works</a>
        <a href="#pricing" onclick="document.getElementById('mob-nav').classList.remove('open')">Pricing</a>
        <div class="mob-menu-actions">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn-ghost">Sign in</a>
                <a href="{{ route('register') }}" class="btn-primary">Get Started</a>
            @endauth
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════ HERO ═══════════════════════════════════════ --}}
<section class="hero">
    <div class="blob" style="width:700px;height:700px;background:rgba(245,158,11,0.07);top:5%;left:50%;transform:translateX(-50%);"></div>
    <div class="blob" style="width:450px;height:450px;background:rgba(234,88,12,0.05);bottom:10%;right:2%;"></div>

    <div style="position:relative;z-index:1;max-width:940px;margin:0 auto;width:100%;">

        @if(session('status') === 'manager-request-submitted')
        <div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);border-radius:var(--radius-sm);padding:14px 20px;margin-bottom:32px;color:#34d399;font-size:14px;display:flex;align-items:center;gap:10px;">
            <i class="fa-solid fa-circle-check"></i>
            {{ __('Registration submitted! Admin will approve your restaurant shortly.') }}
        </div>
        @endif

        {{-- Eyebrow --}}
        <div class="fade-up" style="margin-bottom:28px;">
            <span class="eyebrow"><span style="width:7px;height:7px;border-radius:50%;background:var(--amber);display:inline-block;animation:pulse-dot 2s infinite;"></span> India's #1 Restaurant Reservation Platform</span>
        </div>

        {{-- Headline --}}
        <h1 class="hero-headline fade-up" style="transition-delay:.08s;">
            Reserve Your<br>
            <span class="grad-text" style="font-family:var(--font-s);font-style:italic;">Royal Table</span><br>
            <span class="dim">Instantly</span>
        </h1>

        {{-- Sub --}}
        <p class="hero-sub fade-up" style="transition-delay:.16s;">
            Discover India's finest restaurants, pre-pay securely via Stripe, and get digital invoices — all in one unified workspace.
        </p>

        {{-- CTAs --}}
        <div class="hero-ctas fade-up" style="transition-delay:.24s;">
            <a href="{{ route('register') }}" class="btn-primary">
                <i class="fa-solid fa-calendar-check"></i> Start Dining Free
            </a>
            <a href="#restaurants" class="btn-ghost">
                <i class="fa-solid fa-magnifying-glass icon-amber" style="color:var(--amber);"></i> Browse Restaurants
            </a>
        </div>

        {{-- Stats --}}
        <div class="fade-up" style="transition-delay:.32s;display:flex;justify-content:center;">
            <div class="stats-strip">
                <div class="stat-item">
                    <div class="stat-val">{{ $restaurants->count() }}+</div>
                    <div class="stat-lbl">Fine Bistros</div>
                </div>
                <div class="stat-item">
                    <div class="stat-val">55k+</div>
                    <div class="stat-lbl">Reservations</div>
                </div>
                <div class="stat-item">
                    <div class="stat-val">4.8<i class="fa-solid fa-star" style="font-size:.55em;margin-left:2px;"></i></div>
                    <div class="stat-lbl">Avg Rating</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Dashboard mockup --}}
    @php $preview = $restaurants->take(3); @endphp
    <div class="hero-dashboard fade-up" style="transition-delay:.42s;">
        <div class="db-bar">
            <div class="db-dots"><span class="dot-r"></span><span class="dot-y"></span><span class="dot-g"></span></div>
            <div class="db-url">zaykadining.com/dashboard</div>
            <span class="db-live">● Live</span>
        </div>
        <div class="db-body">
            {{-- KPIs --}}
            <div class="db-card">
                <div class="db-label">Revenue Today</div>
                <div class="db-num col-amber">₹45,850</div>
                <div class="db-sub col-green"><i class="fa-solid fa-arrow-trend-up fa-xs"></i> +12% vs yesterday</div>
            </div>
            <div class="db-card">
                <div class="db-label">Bookings</div>
                <div class="db-num col-sky">24</div>
                <div class="db-sub">3 pending · 21 confirmed</div>
            </div>
            <div class="db-card">
                <div class="db-label">Staff On Duty</div>
                <div class="db-num col-violet">8</div>
                <div class="db-sub">2 managers · 6 staff</div>
            </div>

            {{-- Bookings --}}
            <div class="db-card" style="grid-column:1/span 2;">
                <div class="db-label" style="margin-bottom:12px;">Recent Reservations</div>
                @foreach($preview as $pi => $pr)
                <div class="db-booking-row">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div class="db-thumb">
                            @if($pr->image_path)
                                <img src="{{ asset('storage/'.$pr->image_path) }}" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">
                            @else
                                <i class="fa-solid fa-bowl-food"></i>
                            @endif
                        </div>
                        <div>
                            <div class="db-rname">{{ Str::limit($pr->name, 20) }}</div>
                            <div class="db-rsub">4 guests · 8:00 PM</div>
                        </div>
                    </div>
                    <span class="status-pill {{ $pi === 1 ? 'pill-amber' : 'pill-green' }}">{{ $pi === 1 ? 'PENDING' : 'CONFIRMED' }}</span>
                </div>
                @endforeach
            </div>

            {{-- Floor --}}
            <div class="db-card">
                <div class="floor-lbl">Floor Status</div>
                @foreach([['Table A1',true],['Suite B1',false],['Terrace C1',true]] as [$tn,$free])
                <div class="floor-row">
                    <span class="floor-name"><i class="fa-solid fa-chair" style="color:{{ $free?'#4ade80':'#f59e0b' }};font-size:10px;"></i>{{ $tn }}</span>
                    <span class="status-pill {{ $free?'pill-green':'pill-amber' }}">{{ $free?'FREE':'BUSY' }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ═══ MARQUEE ═══ --}}
<div class="marquee-wrap">
    <div class="marquee-track">
        @foreach(array_merge($restaurants->all(), $restaurants->all()) as $mr)
            <div class="marquee-item">
                <i class="fa-solid fa-circle-dot"></i>
                {{ $mr->name }}
            </div>
        @endforeach
    </div>
</div>

{{-- ═══════════════════════════════════════ RESTAURANTS ═══════════════════════════════════════ --}}
<section class="section section-border" id="restaurants">
    <div class="blob" style="width:500px;height:500px;background:rgba(245,158,11,0.05);top:50%;right:-80px;transform:translateY(-50%);"></div>
    <div class="section-inner">
        <div class="section-header fade-up">
            <span class="eyebrow"><i class="fa-solid fa-star"></i> Curated Establishments</span>
            <h2 class="section-title">Fine Dining <span class="grad-text">Near You</span></h2>
            <p class="section-sub">Explore luxury multicuisine restaurants on Zayka. View menus, check table availability, and book instantly.</p>
            <div style="margin-top:28px;">
                <div class="search-wrap">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" id="search-input" class="search-input" placeholder="Search restaurants or city..." oninput="filterCards(this.value)">
                </div>
            </div>
        </div>

        @php
        $cuisines = ['Awadhi','Mughlai','Rajasthani','South Indian','Coastal Kerala','Dum Biryani','Tandoori','Bengali'];
        @endphp
        <div class="cards-grid" id="cards-grid">
            @forelse($restaurants as $idx => $resto)
            <a href="{{ route('restaurant.landing', ['id' => $resto->id]) }}"
               class="r-card fade-up"
               data-name="{{ strtolower($resto->name) }}"
               data-address="{{ strtolower($resto->address) }}"
               style="transition-delay:{{ $idx * 55 }}ms;">
                {{-- Image --}}
                <div class="r-img-wrap">
                    @if($resto->image_path)
                        <img src="{{ asset('storage/'.$resto->image_path) }}" alt="{{ $resto->name }}" loading="lazy">
                    @else
                        <div style="width:100%;height:100%;background:linear-gradient(135deg,#1a0900,#0a0a0a);display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-bowl-food" style="font-size:3rem;color:rgba(245,158,11,0.2);"></i>
                        </div>
                    @endif
                    <div class="r-img-gradient"></div>
                    <div class="r-img-top">
                        <span class="cuisine-badge">{{ $cuisines[$idx % count($cuisines)] }}</span>
                        <span class="rating-badge"><i class="fa-solid fa-star" style="font-size:9px;"></i> {{ number_format($resto->rating, 1) }}</span>
                    </div>
                    <div class="r-img-title">{{ $resto->name }}</div>
                </div>
                {{-- Body --}}
                <div class="r-body">
                    <p class="r-address">
                        <i class="fa-solid fa-location-dot"></i>
                        <span style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $resto->address }}</span>
                    </p>
                    <hr class="r-divider">
                    <div class="r-footer">
                        <div class="r-meta">
                            <span><i class="fa-solid fa-comment-dots"></i> {{ $resto->reviews_count }} reviews</span>
                        </div>
                        <span class="r-cta">View <i class="fa-solid fa-arrow-right" style="font-size:10px;"></i></span>
                    </div>
                </div>
            </a>
            @empty
            <div class="no-results">
                <i class="fa-solid fa-bowl-food"></i>
                <p>No restaurants registered yet.</p>
            </div>
            @endforelse
        </div>

        <div id="no-match" style="display:none; text-align:center; padding:60px 20px; color:var(--subtle);">
            <i class="fa-solid fa-magnifying-glass" style="font-size:2rem;margin-bottom:14px;display:block;opacity:0.3;"></i>
            <p>No restaurants match your search.</p>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════ FEATURES ═══════════════════════════════════════ --}}
<section class="section section-border" id="features">
    <div class="blob" style="width:500px;height:500px;background:rgba(234,88,12,0.05);top:50%;left:-80px;transform:translateY(-50%);"></div>
    <div class="section-inner">
        <div class="section-header fade-up">
            <span class="eyebrow"><i class="fa-solid fa-shield-halved"></i> Platform Features</span>
            <h2 class="section-title">Everything In <span class="grad-text">One Place</span></h2>
            <p class="section-sub">From real-time table booking to automated invoices — Zayka connects every part of the dining experience.</p>
        </div>

        @php
        $features = [
            ['fa-chair',          'Real-Time Table Booking',   'Reserve seats with live availability. No double-bookings, no manual confirmations. Instant status updates.'],
            ['fa-credit-card',    'Stripe Secure Payments',    'Pay advance deposits or full pre-payment securely via Stripe. Supports test mode for development.'],
            ['fa-file-invoice',   'Instant PDF Invoices',      'Automated PDF invoices sent to customer inboxes on booking confirmation. Zero manual billing effort.'],
            ['fa-user-clock',     'Staff Shift Tracker',       'Digital check-in/out for chefs and servers. Managers see a live duty roster at all times.'],
            ['fa-chart-line',     'Live Analytics Dashboard',  'Real-time revenue, booking counts, and table occupancy KPIs — all in one manager console.'],
            ['fa-shield-halved',  'Role-Based Access Control', 'Purpose-built dashboards for diners, staff, managers, and admins. Powered by Spatie Permissions.'],
        ];
        @endphp
        <div class="feat-grid">
            @foreach($features as $i => $f)
            <div class="feat-card fade-up" style="transition-delay:{{ $i * 75 }}ms;">
                <div class="feat-icon"><i class="fa-solid {{ $f[0] }}"></i></div>
                <h3 class="feat-title">{{ $f[1] }}</h3>
                <p class="feat-desc">{{ $f[2] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════ HOW IT WORKS ═══════════════════════════════════════ --}}
<section class="section section-border" id="how">
    <div class="section-inner">
        <div class="section-header fade-up">
            <span class="eyebrow"><i class="fa-solid fa-route"></i> 3 Simple Steps</span>
            <h2 class="section-title">Book in Under <span class="grad-text">2 Minutes</span></h2>
        </div>

        @php
        $steps = [
            ['01','fa-magnifying-glass','Discover',  'Browse our curated list of premium Indian restaurants. Filter by cuisine, location, and rating with ease.'],
            ['02','fa-calendar-check',  'Reserve',   'Pick date, time, and table. Pay a secure Stripe deposit — no hidden fees, no surprises.'],
            ['03','fa-bowl-food',       'Dine Royal','Arrive and enjoy! Your digital invoice is already in your inbox before you even sit down.'],
        ];
        @endphp
        <div class="steps-grid">
            @foreach($steps as $i => $s)
            <div class="step-card fade-up" style="transition-delay:{{ $i * 120 }}ms;">
                <div class="step-num">{{ $s[0] }}</div>
                <div class="step-icon"><i class="fa-solid {{ $s[1] }}"></i></div>
                <h3 class="step-title">{{ $s[2] }}</h3>
                <p class="step-desc">{{ $s[3] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════ PORTALS ═══════════════════════════════════════ --}}
<section class="section section-border">
    <div class="blob" style="width:500px;height:500px;background:rgba(245,158,11,0.05);bottom:0;right:5%;"></div>
    <div class="section-inner">
        <div class="portals-grid">
            {{-- Left --}}
            <div class="fade-up">
                <span class="eyebrow"><i class="fa-solid fa-layer-group"></i> Unified Workspace</span>
                <h2 class="section-title" style="margin-top:20px;text-align:left;">One Platform,<br><span class="grad-text">Four Dashboards</span></h2>
                <p class="feat-desc" style="margin-top:14px;max-width:420px;">Every stakeholder gets a purpose-built experience tailored precisely to their daily workflow.</p>

                @php
                $portals = [
                    ['fa-user',          'pi-green',  'Diner Portal',     'Browse, reserve tables, track bookings, and download invoices.'],
                    ['fa-clock',         'pi-sky',    'Staff Workspace',   'Digital check-in/out, view today\'s guests, manage assigned tables.'],
                    ['fa-chart-bar',     'pi-amber',  'Manager Console',   'Approve bookings, manage menus, staff rosters, and revenue KPIs.'],
                    ['fa-shield-halved', 'pi-violet', 'SaaS Admin Panel',  'Full system oversight — tenants, roles, permissions, and audits.'],
                ];
                @endphp
                <div class="portal-items">
                    @foreach($portals as $p)
                    <div class="portal-item">
                        <div class="portal-icon {{ $p[1] }}"><i class="fa-solid {{ $p[0] }}"></i></div>
                        <div>
                            <div class="portal-name">{{ $p[2] }}</div>
                            <div class="portal-desc">{{ $p[3] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div style="margin-top:28px;">
                    <a href="{{ route('register') }}" class="btn-primary">
                        <i class="fa-solid fa-user-plus"></i> Create Your Account
                    </a>
                </div>
            </div>

            {{-- Right: Console mockup --}}
            <div class="fade-up" style="transition-delay:.18s;">
                <div class="console-card">
                    <div class="console-bar">
                        <div class="db-dots"><span class="dot-r"></span><span class="dot-y"></span><span class="dot-g"></span></div>
                        <span style="font-size:11px;font-family:monospace;color:var(--subtle);">Manager Console</span>
                        <span class="db-live" style="margin-left:auto;">● Live</span>
                    </div>
                    <div class="console-body">
                        <div class="console-kpi">
                            @foreach([['₹38k','Revenue','col-amber'],['24','Bookings','col-green'],['8','On Duty','col-sky']] as $k)
                            <div class="kpi-box">
                                <div class="kpi-val {{ $k[2] }}">{{ $k[0] }}</div>
                                <div class="kpi-lbl">{{ $k[1] }}</div>
                            </div>
                            @endforeach
                        </div>

                        <div class="floor-card">
                            <div class="floor-lbl">Live Floor Map</div>
                            @foreach([['Royal Table A1',true,'#4ade80'],['Maharaja Suite B1',false,'#f59e0b'],['Haveli Terrace C1',true,'#4ade80']] as [$tn,$free,$c])
                            <div class="floor-row">
                                <span class="floor-name"><i class="fa-solid fa-chair" style="color:{{ $c }};"></i>{{ $tn }}</span>
                                <span class="status-pill {{ $free?'pill-green':'pill-amber' }}">{{ $free?'FREE':'BUSY' }}</span>
                            </div>
                            @endforeach
                        </div>

                        <div class="shift-badge">
                            <span class="shift-dot"></span>
                            <span class="shift-text">Head Chef Devendra · On Shift 4h 20m</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════ PRICING ═══════════════════════════════════════ --}}
<section class="section section-border" id="pricing">
    <div class="blob" style="width:600px;height:600px;background:rgba(245,158,11,0.05);top:50%;left:50%;transform:translate(-50%,-50%);"></div>
    <div class="section-inner">
        <div class="section-header fade-up">
            <span class="eyebrow"><i class="fa-solid fa-tag"></i> Transparent Pricing</span>
            <h2 class="section-title">Simple <span class="grad-text">Plans</span></h2>
            <p class="section-sub">Scale as you grow. No hidden fees, no surprises — cancel anytime.</p>
        </div>

        @php
        $plans = [
            ['Bistro Starter',       '₹1,999', false, 'amber', [
                [true,  '1 Restaurant Profile'],
                [true,  'Up to 8 Seating Tables'],
                [true,  '5 Staff Members'],
                [false, 'Custom Invoice Branding'],
                [false, 'Priority Support'],
            ]],
            ['Imperial Suite',       '₹4,999', true,  'amber', [
                [true,  '3 Restaurant Branches'],
                [true,  'Unlimited Tables'],
                [true,  '25 Staff Members'],
                [true,  'Custom PDF Invoices'],
                [true,  'Priority Support'],
            ]],
            ['Maharaja Enterprise',  '₹9,999', false, 'muted', [
                [true,  'Unlimited Branches'],
                [true,  'Unlimited Tables & Staff'],
                [true,  'API Access & Export'],
                [true,  'White-Label Option'],
                [true,  'Dedicated Account Manager'],
            ]],
        ];
        @endphp
        <div class="pricing-grid">
            @foreach($plans as $p)
            <div class="price-card {{ $p[2] ? 'featured' : '' }} fade-up">
                @if($p[2])<div class="popular-tag">Most Popular</div>@endif
                <div class="price-plan" style="color:{{ $p[2] ? 'var(--amber)' : 'var(--subtle)' }};">{{ $p[0] }}</div>
                <div class="price-val">{{ $p[1] }}</div>
                <div class="price-period">per month · billed annually</div>
                <hr class="price-divider">
                <ul class="price-features">
                    @foreach($p[4] as $feat)
                    <li class="{{ $feat[0] ? '' : 'cross-txt' }}">
                        <i class="fa-solid {{ $feat[0] ? 'fa-circle-check check' : 'fa-circle-xmark cross' }}"></i>
                        {{ $feat[1] }}
                    </li>
                    @endforeach
                </ul>
                @if($p[2])
                    <a href="{{ route('register') }}" class="btn-plan-primary">Get Started</a>
                @else
                    <a href="{{ route('login') }}" class="btn-plan-ghost">Select Plan</a>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════ TESTIMONIALS ═══════════════════════════════════════ --}}
<section class="section section-border">
    <div class="section-inner">
        <div class="section-header fade-up">
            <span class="eyebrow"><i class="fa-solid fa-quote-left"></i> Testimonials</span>
            <h2 class="section-title">Loved by <span class="grad-text">Restaurant Owners</span></h2>
        </div>

        @php
        $quotes = [
            ['#f97316','VS','Vikram Singh','GM · Masala Heritage, Jaipur','The staff attendance tracking and automated invoices are game-changers. No more table overlaps or manual billing. Zayka completely simplified my manager duties.'],
            ['#a78bfa','AK','Ananya Kapoor','Director · The Royal Tandoor, Mumbai','Diners love the Stripe payment option. They pay their advance deposit, get an instant invoice, and our hostesses are notified on the dashboard immediately.'],
            ['#4ade80','RM','Rajan Mehta','Owner · Zayka Haveli, Delhi','Multi-restaurant isolation is incredible. My three branches operate independently yet I see a unified revenue dashboard in the admin panel. Game changer.'],
        ];
        @endphp
        <div class="quotes-grid">
            @foreach($quotes as $qi => $q)
            <div class="quote-card fade-up" style="transition-delay:{{ $qi * 100 }}ms;">
                <div class="stars">
                    @for($s=0;$s<5;$s++)<i class="fa-solid fa-star"></i>@endfor
                </div>
                <p class="quote-text">{{ $q[4] }}</p>
                <div class="quote-author">
                    <div class="author-avatar" style="background:{{ $q[0] }};">{{ $q[2] }}</div>
                    <div>
                        <div class="author-name">{{ $q[2] }}</div>
                        <div class="author-role">{{ $q[3] }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════ CTA ═══════════════════════════════════════ --}}
<section class="section section-border">
    <div class="section-inner">
        <div class="cta-box fade-up">
            <div class="blob" style="width:400px;height:400px;background:rgba(245,158,11,0.08);top:50%;left:50%;transform:translate(-50%,-50%);"></div>
            <div style="position:relative;z-index:1;">
                <span class="eyebrow" style="margin-bottom:22px;display:inline-flex;"><i class="fa-solid fa-rocket"></i> Start Today</span>
                <h2 class="cta-title">
                    Ready to Elevate Your<br>
                    <span class="grad-text" style="font-family:var(--font-s);font-style:italic;">Dining Experience?</span>
                </h2>
                <p class="cta-sub">Join hundreds of Indian fine dining establishments using Zayka Dining to manage reservations and delight guests.</p>
                <div class="cta-btns">
                    <a href="{{ route('register') }}" class="btn-primary">
                        <i class="fa-solid fa-utensils"></i> Onboard Your Restaurant
                    </a>
                    <a href="#restaurants" class="btn-ghost">
                        <i class="fa-solid fa-magnifying-glass" style="color:var(--amber);"></i> Explore Restaurants
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════ FOOTER ═══════════════════════════════════════ --}}
<footer class="footer">
    <div class="footer-inner">
        <div class="footer-grid">
            {{-- Brand --}}
            <div>
                <div class="logo" style="display:inline-flex;">
                    <div class="logo-icon"><i class="fa-solid fa-bowl-rice"></i></div>
                    <div class="logo-text">Zayka <em>Dining</em></div>
                </div>
                <p class="footer-brand-desc">India's premium multi-restaurant reservation SaaS. Real-time tables, automated invoices, and shift management.</p>
                <div class="social-row">
                    @foreach(['fa-instagram','fa-twitter','fa-linkedin-in'] as $s)
                    <a href="#" class="social-btn"><i class="fa-brands {{ $s }}"></i></a>
                    @endforeach
                </div>
            </div>

            {{-- Links --}}
            <div>
                <div class="footer-heading">Product</div>
                <ul class="footer-links">
                    <li><a href="#restaurants">Restaurants</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                    <li><a href="{{ route('blogs') }}">Blogs</a></li>
                    <li><a href="{{ route('about') }}">About Us</a></li>
                    <li><a href="{{ route('contact') }}">Contact</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <div class="footer-heading">Contact</div>
                <div class="footer-contact-items">
                    <div class="footer-contact-item"><i class="fa-solid fa-envelope"></i> support@zaykadining.com</div>
                    <div class="footer-contact-item"><i class="fa-solid fa-phone"></i> +91 11-45000000</div>
                    <div class="footer-contact-item"><i class="fa-solid fa-location-dot"></i> Connaught Place, New Delhi</div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; {{ now()->year }} Zayka Dining · All rights reserved.</p>
            <div class="footer-legal">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

{{-- ═══════════════════════════════════════ SCRIPTS ═══════════════════════════════════════ --}}
<script>
// ── Theme Toggle ──
const body = document.getElementById('body');
const icon = document.getElementById('theme-icon');
let dark = localStorage.getItem('zaykaTheme') !== 'light';

function applyTheme() {
    if (dark) {
        body.classList.remove('light-mode');
        if (icon) icon.className = 'fa-solid fa-moon';
    } else {
        body.classList.add('light-mode');
        if (icon) icon.className = 'fa-solid fa-sun';
    }
}
function toggleTheme() {
    dark = !dark;
    localStorage.setItem('zaykaTheme', dark ? 'dark' : 'light');
    applyTheme();
}
applyTheme();

// ── Restaurant search filter ──
function filterCards(val) {
    const q = val.toLowerCase().trim();
    const cards = document.querySelectorAll('#cards-grid .r-card');
    let visible = 0;
    cards.forEach(card => {
        const name = card.dataset.name || '';
        const addr = card.dataset.address || '';
        const match = !q || name.includes(q) || addr.includes(q);
        card.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('no-match').style.display = (q && visible === 0) ? 'block' : 'none';
}

// ── Scroll fade animations ──
const obs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.classList.add('in');
            obs.unobserve(e.target);
        }
    });
}, { threshold: 0.08 });
document.querySelectorAll('.fade-up').forEach(el => {
    obs.observe(el);
    const r = el.getBoundingClientRect();
    if (r.top < window.innerHeight) el.classList.add('in');
});
</script>

</body>
</html>
