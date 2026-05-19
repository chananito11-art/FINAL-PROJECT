<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','OrangeCrush Car Rentals')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{color-scheme:dark;--orange:#ff6b00;--orange-l:#ff8c3a;--og:rgba(255,107,0,0.25);--dark:#06091b;--dark2:#0d1128;--text:#f0f2ff;--muted:rgba(240,242,255,0.55);--text-dim:rgba(240,242,255,0.45);--line:rgba(255,255,255,0.08);--t:.2s ease;--nav-bg:rgba(6,9,27,.9);--hover-bg:rgba(255,255,255,.07);--ghost-bg:rgba(255,255,255,.07);--ghost-hover:rgba(255,255,255,.12);--card-bg:rgba(255,255,255,.04);--input-bg:rgba(255,255,255,.07);--green:#4ade80;--red:#f87171;--sidebar-w:260px;}
        body.light-mode {
            color-scheme: light;
            --dark: #ffffff;
            --dark2: #f3f4f6;
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
        body{min-height:100%;font-family:'Inter',system-ui,sans-serif;background:var(--dark2);color:var(--text);margin:0}
        
        .layout{display:flex;min-height:100vh}
        
        /* Sidebar */
        .sidebar{width:var(--sidebar-w);background:var(--dark);border-right:1px solid var(--line);display:flex;flex-direction:column;flex-shrink:0;position:fixed;top:0;left:0;height:100vh;z-index:100;overflow-y:auto}
        .sidebar-logo{display:flex;align-items:center;gap:12px;padding:24px 20px;text-decoration:none}
        .logo-icon{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--orange-l),var(--orange));display:grid;place-items:center;box-shadow:0 0 12px var(--og)}
        .logo-text{font-size:1.1rem;font-weight:900;color:var(--text);letter-spacing:-.04em}
        
        .sidebar-nav{flex:1;padding:12px 12px;display:flex;flex-direction:column;gap:4px}
        .nav-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);padding:12px 12px 8px}
        .nav-link{display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:12px;font-size:.9rem;font-weight:500;color:var(--muted);text-decoration:none;transition:all var(--t)}
        .nav-link:hover{background:var(--hover-bg);color:var(--text)}
        .nav-link.active{background:rgba(255,107,0,.1);color:var(--orange-l);font-weight:700}
        .nav-link svg{flex-shrink:0;opacity:.7}
        .nav-link.active svg{opacity:1}
        
        .sidebar-footer{padding:20px 12px;border-top:1px solid var(--line)}
        .user-box{padding:12px;border-radius:14px;background:var(--card-bg);display:flex;align-items:center;gap:10px;margin-bottom:12px;text-decoration:none;color:inherit;transition:background var(--t)}
        .user-box:hover{background:var(--hover-bg)}
        .user-avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--orange-l),var(--orange));display:grid;place-items:center;font-weight:800;font-size:.85rem;color:#fff}
        .user-info{overflow:hidden}
        .user-name{font-size:.85rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        
        /* Main Content */
        .main{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;min-height:100vh}
        .topbar{height:64px;padding:0 32px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--line);background:var(--dark);position:sticky;top:0;z-index:50}
        .topbar-title{font-size:1.1rem;font-weight:800;letter-spacing:-.02em}
        
        .content{flex:1;padding:40px 32px}
        .alert{padding:14px 32px;font-size:.9rem;display:flex;align-items:center;gap:12px;border-bottom:1px solid var(--line)}
        .alert-success{background:rgba(34,197,94,.08);color:var(--green)}
        .alert-error{background:rgba(239,68,68,.08);color:var(--red)}
        .alert-warning{background:rgba(245,158,11,.1);color:#f59e0b;border-bottom:1px solid rgba(245,158,11,.2)}
        
        .btn{display:inline-flex;align-items:center;gap:8px;padding:9px 18px;border-radius:10px;font-size:.88rem;font-weight:600;cursor:pointer;border:none;font-family:inherit;transition:all var(--t);text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,var(--orange-l),var(--orange));color:#fff;box-shadow:0 4px 14px var(--og)}
        .btn-primary:hover{filter:brightness(1.1);transform:translateY(-1px)}
        .btn-ghost{background:var(--ghost-bg);color:var(--text);border:1px solid var(--line)}
        .btn-ghost:hover{background:var(--ghost-hover)}
        .btn-sm{padding:6px 12px;font-size:.82rem}

        /* Forms */
        .form-group{margin-bottom:20px}
        .form-label{display:block;font-size:.8rem;font-weight:700;color:var(--text-dim);margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em}
        .form-control{width:100%;height:46px;background:var(--input-bg);border:1px solid var(--line);border-radius:14px;color:var(--text);font-family:inherit;font-size:.95rem;padding:0 16px;outline:none;transition:all .25s cubic-bezier(0.4, 0, 0.2, 1);box-shadow:inset 0 2px 4px rgba(0,0,0,0.02)}
        .form-control:focus{border-color:var(--orange);box-shadow:0 0 0 4px rgba(255,107,0,0.12), inset 0 2px 4px rgba(0,0,0,0.02);transform:translateY(-1px);background-color:var(--dark)}
        .form-control::placeholder{color:var(--text-dim);opacity:0.6}
        select.form-control{-webkit-appearance:none;cursor:pointer;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='rgba(240,242,255,0.5)' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;background-size:16px 16px;padding-right:44px}
        body.light-mode select.form-control{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='rgba(17,24,39,0.5)' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E")}
        select.form-control option{background:var(--dark2);color:var(--text)}
        textarea.form-control{height:auto;padding:14px 16px;min-height:100px;resize:vertical}

        @media (max-width: 900px) {
            .sidebar{transform:translateX(-100%);transition:transform .3s ease;width:240px}
            .sidebar.open{transform:translateX(0)}
            .main{margin-left:0}
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="layout">
    <aside class="sidebar" id="sidebar">
        <a href="/" class="sidebar-logo">
            <div class="logo-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M3 13l1.5-4.5A3 3 0 0 1 7.35 6h9.3a3 3 0 0 1 2.85 2.5L21 13"/><path d="M5 16h14"/><circle cx="7.5" cy="15.5" r="1.5"/><circle cx="16.5" cy="15.5" r="1.5"/></svg>
            </div>
            <span class="logo-text">OrangeCrush</span>
        </a>
        
        <nav class="sidebar-nav">
            @auth
            <span class="nav-label">General</span>
            <a href="{{ route('customer.dashboard') }}" class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg> Dashboard
            </a>
            <a href="{{ route('customer.tracking.index') }}" class="nav-link {{ request()->routeIs('customer.tracking.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg> My Bookings
            </a>
            <a href="{{ route('customer.payments.index') }}" class="nav-link {{ request()->routeIs('customer.payments.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg> Payment
            </a>
            <a href="{{ route('customer.transactions.index') }}" class="nav-link {{ request()->routeIs('customer.transactions.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg> Transaction History
            </a>
            @endauth
            
            <span class="nav-label">Explore</span>
            <a href="{{ route('vehicles.index') }}" class="nav-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18.5" cy="17.5" r="3.5"/><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="12" cy="5" r="3"/><path d="M12 2v3"/><path d="M12 8v3"/><path d="M3 17.5c0-3.5 3-5.5 9-5.5s9 2 9 5.5"/></svg> All Vehicles
            </a>
            
            @guest
            <div style="margin-top: 20px; padding: 0 12px">
                <a href="{{ route('login') }}" class="btn btn-ghost" style="width: 100%; justify-content: center; margin-bottom: 8px">Sign In</a>
                <a href="{{ route('register') }}" class="btn btn-primary" style="width: 100%; justify-content: center">Sign Up</a>
            </div>
            @endguest
        </nav>
        
        <div class="sidebar-footer">
            @auth
            <a href="{{ route('customer.profile.edit') }}" class="user-box">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->first_name }}</div>
                    <div style="font-size: .7rem; color: var(--muted)">Edit Profile</div>
                </div>
            </a>
            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost btn-sm" style="width: 100%; justify-content: center; margin-bottom: 8px">Admin Panel</a>
            @endif
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm" style="width: 100%; justify-content: center; color: var(--red)">Logout</button>
            </form>
            @endauth
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div style="display: flex; align-items: center; gap: 12px">
                <span class="topbar-title">@yield('title_display', 'OrangeCrush')</span>
            </div>
            <div style="display: flex; gap: 12px; align-items: center">
                <button id="themeToggle" class="btn btn-ghost btn-sm" style="padding: 6px 10px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                    </svg>
                </button>
            </div>
        </header>
        
        @if(session('success'))<div class="alert alert-success">✓ {{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-error">✕ {{ session('error') }}</div>@endif
        @if(session('warning'))<div class="alert alert-warning">⚠ {{ session('warning') }}</div>@endif
        
        <div class="content">
            @yield('content')
        </div>
    </main>
</div>

@stack('scripts')
<script>
    const themeBtn = document.getElementById('themeToggle');
    const body = document.body;
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'light') body.classList.add('light-mode');
    else if (!savedTheme && window.matchMedia('(prefers-color-scheme: light)').matches) body.classList.add('light-mode');

    themeBtn.addEventListener('click', () => {
        body.classList.toggle('light-mode');
        localStorage.setItem('theme', body.classList.contains('light-mode') ? 'light' : 'dark');
    });
</script>
</body>
</html>
