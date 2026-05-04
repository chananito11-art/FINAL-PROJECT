<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'OrangeCrush') — Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--orange:#ff6b00;--orange-l:#ff8c3a;--og:rgba(255,107,0,0.25);--dark:#06091b;--dark2:#0d1128;--line:rgba(255,255,255,0.08);--text:#f0f2ff;--muted:rgba(240,242,255,0.55);--sidebar-w:240px;--r:14px;--t:.2s ease;--green:#22c55e;--red:#ef4444;--yellow:#f59e0b;--blue:#3b82f6;--card-bg:rgba(255,255,255,.04);--hover-bg:rgba(255,255,255,.05);--input-bg:rgba(255,255,255,.06);--ghost-bg:rgba(255,255,255,.06);--ghost-hover:rgba(255,255,255,.1);--badge-y:rgba(255,255,255,.08);}
        @media (prefers-color-scheme: light) {
            :root {
                --dark: #ffffff;
                --dark2: #f3f4f6;
                --line: rgba(0,0,0,0.1);
                --text: #111827;
                --muted: rgba(17,24,39,0.6);
                --card-bg: #ffffff;
                --hover-bg: rgba(0,0,0,0.04);
                --input-bg: #ffffff;
                --ghost-bg: #f3f4f6;
                --ghost-hover: #e5e7eb;
                --badge-y: rgba(0,0,0,0.05);
                --og: rgba(255,107,0,0.15);
            }
        }
        html,body{height:100%;font-family:'Inter',system-ui,sans-serif;background:var(--dark2);color:var(--text)}
        .layout{display:flex;min-height:100vh}
        .sidebar{width:var(--sidebar-w);background:var(--dark);border-right:1px solid var(--line);display:flex;flex-direction:column;flex-shrink:0;position:fixed;top:0;left:0;height:100vh;z-index:100;overflow-y:auto}
        .sidebar-logo{display:flex;align-items:center;gap:10px;padding:20px 20px 16px;border-bottom:1px solid var(--line);text-decoration:none}
        .logo-icon{width:36px;height:36px;border-radius:10px;flex-shrink:0;background:linear-gradient(135deg,var(--orange-l),var(--orange));display:grid;place-items:center;box-shadow:0 0 14px var(--og)}
        .logo-text{font-size:1rem;font-weight:800;color:var(--text);letter-spacing:-.03em}
        .logo-badge{font-size:.65rem;font-weight:700;background:rgba(255,107,0,.18);color:var(--orange-l);padding:2px 6px;border-radius:6px;margin-left:auto;border:1px solid rgba(255,107,0,.25);text-transform:uppercase;letter-spacing:.05em}
        .sidebar-nav{flex:1;padding:12px 10px;display:flex;flex-direction:column;gap:2px}
        .nav-label{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);padding:12px 10px 6px}
        .nav-link{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:10px;font-size:.9rem;font-weight:500;color:var(--muted);text-decoration:none;transition:background var(--t),color var(--t)}
        .nav-link:hover{background:var(--hover-bg);color:var(--text)}
        .nav-link.active{background:rgba(255,107,0,.12);color:var(--orange-l);font-weight:600}
        .nav-link svg{flex-shrink:0;opacity:.7}
        .nav-link.active svg{opacity:1}
        .sidebar-footer{padding:12px 10px;border-top:1px solid var(--line)}
        .user-card{display:flex;align-items:center;gap:10px;padding:10px;border-radius:10px;background:var(--card-bg)}
        .user-avatar{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--orange-l),var(--orange));display:grid;place-items:center;font-weight:800;font-size:.9rem;flex-shrink:0}
        .user-name{font-size:.88rem;font-weight:600;line-height:1.2}
        .user-role{font-size:.75rem;color:var(--muted)}
        .logout-btn{margin-left:auto;background:none;border:none;cursor:pointer;color:var(--muted);padding:4px;border-radius:6px;transition:color var(--t)}
        .logout-btn:hover{color:var(--red)}
        .main{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;min-height:100vh}
        .topbar{height:60px;border-bottom:1px solid var(--line);display:flex;align-items:center;padding:0 28px;gap:16px;background:var(--dark);position:sticky;top:0;z-index:50}
        .topbar-title{font-size:1.05rem;font-weight:700}
        .topbar-right{margin-left:auto;display:flex;align-items:center;gap:12px}
        .content{flex:1;padding:28px}
        .card{background:var(--card-bg);border:1px solid var(--line);border-radius:16px;overflow:hidden}
        .card-header{padding:18px 22px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:12px}
        .card-title{font-size:1rem;font-weight:700}
        .card-body{padding:22px}
        .stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;margin-bottom:24px}
        .stat-card{background:var(--card-bg);border:1px solid var(--line);border-radius:16px;padding:20px;position:relative;overflow:hidden}
        .stat-label{font-size:.8rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px}
        .stat-value{font-size:2rem;font-weight:900;letter-spacing:-.05em}
        .stat-card.orange{border-color:rgba(255,107,0,.2);background:rgba(255,107,0,.07)}
        .stat-card.orange .stat-value{color:var(--orange-l)}
        .tw{overflow-x:auto}
        table{width:100%;border-collapse:collapse;font-size:.9rem}
        th{padding:12px 16px;text-align:left;font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);border-bottom:1px solid var(--line);white-space:nowrap}
        td{padding:13px 16px;border-bottom:1px solid var(--line);vertical-align:middle}
        tr:last-child td{border-bottom:none}
        tr:hover td{background:var(--hover-bg)}
        .badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:.78rem;font-weight:700}
        .bo{background:rgba(255,107,0,.15);color:var(--orange-l)}
        .bg_{background:rgba(34,197,94,.15);color:var(--green)}
        .br{background:rgba(239,68,68,.15);color:var(--red)}
        .by{background:rgba(245,158,11,.15);color:var(--yellow)}
        .bb{background:rgba(59,130,246,.15);color:var(--blue)}
        .bgy{background:var(--badge-y);color:var(--muted)}
        .btn{display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border-radius:10px;font-size:.88rem;font-weight:600;cursor:pointer;border:none;font-family:inherit;transition:all var(--t);text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,var(--orange-l),var(--orange));color:#fff;box-shadow:0 4px 12px var(--og)}
        .btn-primary:hover{filter:brightness(1.1);transform:translateY(-1px)}
        .btn-ghost{background:var(--ghost-bg);color:var(--text);border:1px solid var(--line)}
        .btn-ghost:hover{background:var(--ghost-hover)}
        .btn-danger{background:rgba(239,68,68,.15);color:var(--red);border:1px solid rgba(239,68,68,.25)}
        .btn-danger:hover{background:rgba(239,68,68,.25)}
        .btn-sm{padding:5px 11px;font-size:.8rem}
        .btn-success{background:rgba(34,197,94,.15);color:var(--green);border:1px solid rgba(34,197,94,.25)}
        .btn-success:hover{background:rgba(34,197,94,.25)}
        .form-group{margin-bottom:18px}
        .form-label{display:block;font-size:.83rem;font-weight:600;color:var(--muted);margin-bottom:7px;text-transform:uppercase;letter-spacing:.04em}
        .form-control{width:100%;height:42px;background:var(--input-bg);border:1px solid var(--line);border-radius:10px;color:var(--text);font-family:inherit;font-size:.95rem;padding:0 14px;outline:none;transition:border-color var(--t),box-shadow var(--t)}
        .form-control:focus{border-color:rgba(255,107,0,.5);box-shadow:0 0 0 3px rgba(255,107,0,.12)}
        select.form-control{-webkit-appearance:none;cursor:pointer}
        textarea.form-control{height:auto;padding:12px 14px;resize:vertical}
        .alert{padding:12px 16px;border-radius:10px;font-size:.9rem;margin-bottom:20px;display:flex;align-items:center;gap:10px}
        .alert-success{background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.25);color:var(--green)}
        .alert-error{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);color:var(--red)}
        .g2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .g3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}
        .flex{display:flex;align-items:center;gap:12px}
        .ml-auto{margin-left:auto}
    </style>
    @stack('styles')
</head>
<body>
<div class="layout">
<aside class="sidebar">
    <a href="/" class="sidebar-logo">
        <div class="logo-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 13l1.5-4.5A3 3 0 0 1 7.35 6h9.3a3 3 0 0 1 2.85 2.5L21 13"/><path d="M5 16h14"/><path d="M6 16v2a1 1 0 0 0 1 1h1"/><path d="M16 19h1a1 1 0 0 0 1-1v-2"/><circle cx="7.5" cy="15.5" r="1.5"/><circle cx="16.5" cy="15.5" r="1.5"/></svg>
        </div>
        <span class="logo-text">OrangeCrush</span>
        @if(auth()->user()?->isSuperAdmin())<span class="logo-badge">Super</span>
        @elseif(auth()->user()?->isAdmin())<span class="logo-badge">Admin</span>@endif
    </a>
    <nav class="sidebar-nav">
        @if(auth()->user()?->isAdmin())
        <span class="nav-label">Overview</span>
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>Dashboard
        </a>
        <span class="nav-label">Management</span>
        <a href="{{ route('admin.bookings.index') }}" class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>Bookings
        </a>
        <a href="{{ route('admin.vehicles.index') }}" class="nav-link {{ request()->routeIs('admin.vehicles.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 13l1.5-4.5A3 3 0 0 1 7.35 6h9.3a3 3 0 0 1 2.85 2.5L21 13"/><path d="M5 16h14"/><circle cx="7.5" cy="15.5" r="1.5"/><circle cx="16.5" cy="15.5" r="1.5"/></svg>Vehicles
        </a>
        <a href="{{ route('admin.payments.index') }}" class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>Payments
        </a>
        <a href="{{ route('admin.returns.index') }}" class="nav-link {{ request()->routeIs('admin.returns.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.56"/></svg>Returns
        </a>
        <a href="{{ route('admin.logs.index') }}" class="nav-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>Audit Logs
        </a>
        @endif
        @if(auth()->user()?->isSuperAdmin())
        <span class="nav-label">Super Admin</span>
        <a href="{{ route('super-admin.users.index') }}" class="nav-link {{ request()->routeIs('super-admin.users.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>Users
        </a>
        <a href="{{ route('super-admin.terms.edit') }}" class="nav-link {{ request()->routeIs('super-admin.terms.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Terms & Conditions
        </a>
        @endif
    </nav>
    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()?->first_name ?? 'U',0,1)) }}</div>
            <div>
                <div class="user-name">{{ auth()->user()?->first_name }}</div>
                <div class="user-role">{{ auth()->user()?->getRoleNames()->first() ?? 'admin' }}</div>
            </div>
            <form method="POST" action="/logout" style="margin-left:auto">@csrf
                <button type="submit" class="logout-btn" title="Logout">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>
<div class="main">
    <header class="topbar">
        <span class="topbar-title">@yield('page-title','Dashboard')</span>
        <div class="topbar-right"><a href="/" class="btn btn-ghost btn-sm">← Public Site</a></div>
    </header>
    <div class="content">
        @if(session('success'))<div class="alert alert-success">✓ {{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-error">✕ {{ session('error') }}</div>@endif
        @yield('content')
    </div>
</div>
</div>
@stack('scripts')
</body>
</html>
