<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','OrangeCrush Car Rentals')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{color-scheme:dark;--orange:#ff6b00;--orange-l:#ff8c3a;--og:rgba(255,107,0,0.25);--dark:#06091b;--text:#f0f2ff;--muted:rgba(240,242,255,0.55);--text-dim:rgba(240,242,255,0.45);--line:rgba(255,255,255,0.08);--t:.2s ease;--nav-bg:rgba(6,9,27,.9);--hover-bg:rgba(255,255,255,.07);--ghost-bg:rgba(255,255,255,.07);--ghost-hover:rgba(255,255,255,.12);--card-bg:rgba(255,255,255,.04);--input-bg:rgba(255,255,255,.07);--green:#4ade80;--red:#f87171;}
        body.light-mode {
            color-scheme: light;
            --dark: #f9fafb;
            --text: #111827;
            --muted: rgba(17,24,39,0.6);
            --text-dim: rgba(17,24,39,0.45);
            --line: rgba(0,0,0,0.1);
            --nav-bg: rgba(255,255,255,.9);
            --hover-bg: rgba(0,0,0,0.05);
            --ghost-bg: #f3f4f6;
            --ghost-hover: #e5e7eb;
            --card-bg: #ffffff;
            --input-bg: #ffffff;
            --og: rgba(255,107,0,0.15);
            --green:#16a34a;
            --red:#dc2626;
        }
        html{height:100%}
        body{min-height:100%;font-family:'Inter',system-ui,sans-serif;background:var(--dark);color:var(--text);margin:0}
        .navbar{display:flex;align-items:center;gap:20px;padding:16px 32px;border-bottom:1px solid var(--line);background:var(--nav-bg);backdrop-filter:blur(12px);position:sticky;top:0;z-index:100}
        .nav-brand{display:flex;align-items:center;gap:10px;text-decoration:none}
        .nav-icon{width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg,var(--orange-l),var(--orange));display:grid;place-items:center;box-shadow:0 0 12px var(--og)}
        .nav-name{font-size:.98rem;font-weight:800;color:var(--text);letter-spacing:-.03em}
        .nav-links{display:flex;align-items:center;gap:4px;margin-left:24px}
        .nav-link{padding:7px 13px;border-radius:8px;font-size:.9rem;font-weight:500;color:var(--muted);text-decoration:none;transition:background var(--t),color var(--t)}
        .nav-link:hover,.nav-link.active{background:var(--hover-bg);color:var(--text)}
        .nav-right{margin-left:auto;display:flex;align-items:center;gap:10px}
        .btn{display:inline-flex;align-items:center;gap:7px;padding:8px 18px;border-radius:10px;font-size:.88rem;font-weight:600;cursor:pointer;border:none;font-family:inherit;transition:all var(--t);text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,var(--orange-l),var(--orange));color:#fff;box-shadow:0 4px 14px var(--og)}
        .btn-primary:hover{filter:brightness(1.1);transform:translateY(-1px)}
        .btn-ghost{background:var(--ghost-bg);color:var(--text);border:1px solid var(--line)}
        .btn-ghost:hover{background:var(--ghost-hover)}
        .btn-sm{padding:6px 14px;font-size:.83rem}
        .page-content{min-height:calc(100vh - 65px)}
        .alert{padding:12px 20px;font-size:.9rem;display:flex;align-items:center;gap:10px;border-bottom:1px solid var(--line)}
        .alert-success{background:rgba(34,197,94,.1);color:var(--green)}
        .alert-error{background:rgba(239,68,68,.1);color:var(--red)}
        .footer{padding:32px;text-align:center;color:var(--muted);font-size:.85rem;border-top:1px solid var(--line)}
        .container{width:min(100%,1200px);margin:0 auto;padding:0 24px}
    </style>
    @stack('styles')
</head>
<body>
<nav class="navbar">
    <a href="/" class="nav-brand">
        <div class="nav-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M3 13l1.5-4.5A3 3 0 0 1 7.35 6h9.3a3 3 0 0 1 2.85 2.5L21 13"/><path d="M5 16h14"/><circle cx="7.5" cy="15.5" r="1.5"/><circle cx="16.5" cy="15.5" r="1.5"/></svg>
        </div>
        <span class="nav-name">OrangeCrush</span>
    </a>
    <div class="nav-links">
        <a href="/" class="nav-link {{ request()->is('/') ? 'active' : '' }}">Home</a>
        <a href="{{ route('vehicles.index') }}" class="nav-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">Vehicles</a>
        @auth<a href="{{ route('customer.tracking.index') }}" class="nav-link {{ request()->routeIs('customer.tracking.*') ? 'active' : '' }}">My Bookings</a>@endauth
    </div>
    <div class="nav-right">
        <button id="themeToggle" class="btn btn-ghost btn-sm" title="Toggle Light/Dark Mode" style="padding: 6px 10px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
        </button>
        @guest
            <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Sign In</a>
            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Sign Up</a>
        @else
            @if(auth()->user()->isAdmin())<a href="{{ route('admin.dashboard') }}" class="btn btn-ghost btn-sm">Admin Panel</a>@endif
            <span style="font-size:.88rem;color:var(--muted)">{{ auth()->user()->first_name }}</span>
            <form method="POST" action="/logout">@csrf<button type="submit" class="btn btn-ghost btn-sm">Logout</button></form>
        @endguest
    </div>
</nav>
@if(session('success'))<div class="alert alert-success">✓ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-error">✕ {{ session('error') }}</div>@endif
<div class="page-content">@yield('content')</div>
<footer class="footer">© {{ date('Y') }} OrangeCrush Car Rentals. All rights reserved.</footer>
@stack('scripts')
<script>
    const themeBtn = document.getElementById('themeToggle');
    const body = document.body;
    
    // Check for saved theme or system preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'light') {
        body.classList.add('light-mode');
    } else if (!savedTheme && window.matchMedia('(prefers-color-scheme: light)').matches) {
        body.classList.add('light-mode');
    }

    themeBtn.addEventListener('click', () => {
        body.classList.toggle('light-mode');
        const isLight = body.classList.contains('light-mode');
        localStorage.setItem('theme', isLight ? 'light' : 'dark');
    });
</script>
</body>
</html>
