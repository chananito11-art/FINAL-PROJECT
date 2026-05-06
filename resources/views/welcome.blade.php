<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OrangeCrush Car rentals</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --surface: #ffffff;
            --surface-soft: #f4f6fb;
            --page: #fafafa;
            --text: #09101f;
            --muted: #687287;
            --line: #dde3ec;
            --dark: #06091b;
            --shadow: 0 12px 32px rgba(14, 18, 33, 0.08);
            --radius: 22px;
        }
        body.dark-mode {
            --surface: #0d1128;
            --surface-soft: #141932;
            --page: #06091b;
            --text: #f0f2ff;
            --muted: rgba(240, 242, 255, 0.55);
            --line: rgba(255, 255, 255, 0.08);
            --shadow: 0 12px 32px rgba(0, 0, 0, 0.4);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--page);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        .hero-banner {
            min-height: 362px;
            position: relative;
            background:
                linear-gradient(rgba(10, 13, 22, 0.52), rgba(10, 13, 22, 0.52)),
                url('https://images.unsplash.com/photo-1553440569-bcc63803a83d?auto=format&fit=crop&w=1800&q=80') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 48px 20px;
        }

        .hero-inner {
            max-width: 820px;
        }

        .hero-icon {
            width: 44px;
            height: 44px;
            margin: 0 auto 10px;
            opacity: 0.95;
        }

        .hero-banner h1 {
            margin: 0 0 12px;
            font-size: clamp(2.4rem, 6vw, 4rem);
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .hero-banner p {
            margin: 0;
            font-size: clamp(1rem, 2vw, 1.2rem);
            color: rgba(255, 255, 255, 0.92);
        }

        .page-shell {
            width: min(100%, 1600px);
            margin: 0 auto;
            padding: 0 16px 40px;
        }

        .filter-card {
            margin-top: 14px;
            background: var(--surface);
            border: 1px solid #edf1f6;
            border-radius: 18px;
            box-shadow: var(--shadow);
            padding: 26px;
        }

        .filter-title,
        .section-title {
            margin: 0 0 18px;
            font-size: clamp(1.8rem, 4vw, 2.2rem);
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr;
            gap: 16px;
        }

        .filter-field {
            position: relative;
        }

        .filter-field svg {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: #8e97a9;
        }

        .filter-control {
            width: 100%;
            height: 44px;
            border: 0;
            border-radius: 12px;
            background: var(--surface-soft);
            padding: 0 16px;
            color: var(--text);
            font-size: 1rem;
        }

        .filter-control.search {
            padding-left: 48px;
        }

        .filter-control:focus {
            outline: 2px solid rgba(9, 16, 31, 0.14);
        }

        .vehicles-section {
            padding-top: 48px;
        }

        .system-section {
            padding-top: 48px;
        }

        .section-subtitle {
            margin: 0 0 28px;
            color: var(--muted);
            font-size: 0.98rem;
        }

        .journey-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 28px;
        }

        .journey-card,
        .role-card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 24px;
            box-shadow: var(--shadow);
            padding: 26px;
        }

        .journey-card h3,
        .role-card h3 {
            margin: 0 0 14px;
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .journey-list {
            display: grid;
            gap: 14px;
        }

        .journey-step {
            display: grid;
            grid-template-columns: 48px 1fr;
            gap: 14px;
            align-items: start;
            padding: 16px;
            border-radius: 18px;
            background: var(--surface-soft);
        }

        .journey-step-number {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-weight: 800;
            color: white;
            background: linear-gradient(135deg, #ff8c3a 0%, #ff6b00 100%);
        }

        .journey-step strong {
            display: block;
            margin-bottom: 4px;
            font-size: 1rem;
        }

        .journey-step p,
        .role-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.55;
        }

        .module-stack {
            display: grid;
            gap: 12px;
            margin-top: 18px;
        }

        .module-pill {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: center;
            padding: 14px 16px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(255, 122, 24, 0.12) 0%, rgba(255, 122, 24, 0.06) 100%);
            border: 1px solid rgba(255, 122, 24, 0.16);
        }

        .module-pill span {
            font-weight: 800;
        }

        .module-pill small {
            color: var(--muted);
            font-size: 0.85rem;
            text-align: right;
        }

        .vehicle-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 28px;
        }

        .vehicle-card {
            border: 1px solid var(--line);
            border-radius: var(--radius);
            overflow: hidden;
            background: var(--surface);
            box-shadow: 0 2px 8px rgba(16, 24, 40, 0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .vehicle-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 34px rgba(16, 24, 40, 0.08);
        }

        .vehicle-media {
            position: relative;
            aspect-ratio: 16 / 10;
            background: #dfe5ef;
            overflow: hidden;
        }

        .vehicle-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .vehicle-pill {
            position: absolute;
            top: 14px;
            right: 14px;
            background: #080b1d;
            color: white;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1;
        }

        .vehicle-body {
            padding: 22px 20px 18px;
        }

        .vehicle-name {
            margin: 0;
            font-size: 1.02rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .vehicle-brand {
            margin-top: 6px;
            color: var(--muted);
            font-size: 0.96rem;
        }

        .vehicle-specs {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            margin-top: 16px;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .vehicle-spec {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .vehicle-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 16px;
        }

        .vehicle-tag {
            background: #eef2f8;
            color: var(--dark);
            border-radius: 999px;
            padding: 7px 12px;
            font-size: 0.88rem;
            font-weight: 700;
            line-height: 1;
        }

        .vehicle-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 16px;
            margin-top: 34px;
        }

        .vehicle-price {
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .vehicle-price small {
            display: block;
            margin-top: 2px;
            color: var(--muted);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .book-btn {
            border: 0;
            background: var(--dark);
            color: white;
            border-radius: 14px;
            padding: 14px 22px;
            font-size: 0.95rem;
            font-weight: 800;
            line-height: 1;
            text-decoration: none;
        }

        .book-btn:hover {
            background: #141a34;
            color: white;
        }

        .hidden-card {
            display: none;
        }

        @media (max-width: 1100px) {
            .vehicle-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 820px) {
            .filter-grid,
            .vehicle-grid {
                grid-template-columns: 1fr;
            }

            .hero-banner {
                min-height: 280px;
            }

            .filter-card {
                padding: 20px;
            }
        }

        /* ── Top Navbar ── */
        .top-nav {
            position: absolute;
            top: 0; left: 0; right: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 36px;
            background: rgba(6, 9, 27, 0.45);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }

        .nav-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: white;
            font-weight: 800;
            font-size: 1.08rem;
            letter-spacing: -0.02em;
        }

        .nav-brand-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #ff8c3a, #ff6b00);
            border-radius: 10px;
            display: grid;
            place-items: center;
            box-shadow: 0 0 16px rgba(255,107,0,0.4);
            flex-shrink: 0;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-greeting {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.7);
            margin-right: 4px;
        }

        .nav-role-badge {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 999px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .nav-role-badge.admin {
            background: rgba(255,107,0,0.2);
            color: #ff8c3a;
            border: 1px solid rgba(255,107,0,0.35);
        }

        .nav-role-badge.user {
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.75);
            border: 1px solid rgba(255,255,255,0.15);
        }

        .btn-nav {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 20px;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            line-height: 1;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-nav-ghost {
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.18);
        }

        .btn-nav-ghost:hover {
            background: rgba(255,255,255,0.18);
            color: white;
            text-decoration: none;
        }

        .btn-nav-orange {
            background: linear-gradient(135deg, #ff8c3a 0%, #ff6b00 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(255,107,0,0.35);
        }

        .btn-nav-orange:hover {
            filter: brightness(1.1);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(255,107,0,0.45);
            color: white;
            text-decoration: none;
        }

        .btn-nav-dark {
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.7);
            border: 1px solid rgba(255,255,255,0.12);
        }

        .btn-nav-dark:hover {
            background: rgba(255,80,80,0.18);
            color: #ff9090;
            border-color: rgba(255,80,80,0.25);
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .top-nav {
                padding: 14px 18px;
            }
            .nav-greeting { display: none; }
            .btn-nav { padding: 8px 14px; font-size: 0.83rem; }
        }

        /* Offset hero so navbar doesn't overlap content */
        .hero-banner {
            position: relative;
        }
    </style>
</head>
<body>
    <section class="hero-banner">
        {{-- ── Sticky Navbar ── --}}
        <nav class="top-nav" id="topNav" aria-label="Main navigation">
            <a href="/" class="nav-brand" id="navBrand">
                <div class="nav-brand-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 13l1.5-4.5A3 3 0 0 1 7.35 6h9.3a3 3 0 0 1 2.85 2.5L21 13"/>
                        <path d="M5 16h14"/><path d="M6 16v2a1 1 0 0 0 1 1h1"/>
                        <path d="M16 19h1a1 1 0 0 0 1-1v-2"/>
                        <circle cx="7.5" cy="15.5" r="1.5"/><circle cx="16.5" cy="15.5" r="1.5"/>
                    </svg>
                </div>
                OrangeCrush
            </a>

            <div class="nav-actions">
                <button id="themeToggle" class="btn-nav btn-nav-ghost" title="Toggle Light/Dark Mode" style="padding: 9px 12px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                    </svg>
                </button>
                @auth
                    <span class="nav-greeting">Hi, {{ Auth::user()->first_name }}</span>
                    <span class="nav-role-badge">{{ ucfirst(str_replace('_',' ', Auth::user()->getRoleNames()->first() ?? '')) }}</span>
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn-nav btn-nav-ghost" id="navDashboard">Dashboard</a>
                    @endif
                    <a href="{{ route('customer.tracking.index') }}" class="btn-nav btn-nav-ghost" id="navMyBookings">My Bookings</a>
                    <form method="POST" action="/logout" style="margin:0;" id="navLogoutForm">
                        @csrf
                        <button type="submit" class="btn-nav btn-nav-dark" id="navLogout">Sign Out</button>
                    </form>
                @else
                    <a href="/login" class="btn-nav btn-nav-ghost" id="navSignIn">Sign In</a>
                    <a href="/register" class="btn-nav btn-nav-orange" id="navSignUp">Sign Up</a>
                @endauth
            </div>
        </nav>
        <div class="hero-inner">
            <svg class="hero-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 13l1.5-4.5A3 3 0 0 1 7.35 6h9.3a3 3 0 0 1 2.85 2.5L21 13"></path>
                <path d="M5 16h14"></path>
                <path d="M6 16v2a1 1 0 0 0 1 1h1"></path>
                <path d="M16 19h1a1 1 0 0 0 1-1v-2"></path>
                <circle cx="7.5" cy="15.5" r="1.5"></circle>
                <circle cx="16.5" cy="15.5" r="1.5"></circle>
            </svg>
            <h1>OrangeCrush Car rentals</h1>
            <p>Find the perfect vehicle for your journey</p>
        </div>
    </section>

    <main class="page-shell">
        <section class="filter-card" id="fleet">
            <h2 class="filter-title">Search &amp; Filter</h2>
            <div class="filter-grid">
                <div class="filter-field">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="M20 20l-3.5-3.5"></path>
                    </svg>
                    <input class="filter-control search" id="vehicleSearch" type="text" placeholder="Search by car name or brand...">
                </div>
                <div class="filter-field">
                    <select class="filter-control" id="typeFilter">
                        <option value="">All Types</option>
                        @foreach($vehicles->pluck('type')->unique()->values() as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <select class="filter-control" id="transmissionFilter">
                        <option value="">All Transmissions</option>
                        @foreach($vehicles->pluck('transmission')->unique()->values() as $transmission)
                            <option value="{{ $transmission }}">{{ $transmission }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <section class="vehicles-section">
            <h2 class="section-title">Available Vehicles</h2>
            <p class="section-subtitle"><span id="vehicleCount">{{ $vehicles->count() }}</span> vehicles available</p>

            <div class="vehicle-grid" id="vehicleGrid">
                @foreach($vehicles as $vehicle)
                    <article
                        class="vehicle-card"
                        data-name="{{ strtolower($vehicle->name) }}"
                        data-brand="{{ strtolower($vehicle->brand ?? '') }}"
                        data-type="{{ strtolower($vehicle->type) }}"
                        data-transmission="{{ strtolower($vehicle->transmission) }}"
                    >
                        <div class="vehicle-media">
                            <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->name }}">
                            <span class="vehicle-pill">{{ $vehicle->type }}</span>
                        </div>

                        <div class="vehicle-body">
                            <h3 class="vehicle-name">{{ $vehicle->name }}</h3>
                            <div class="vehicle-brand">{{ $vehicle->brand ?? $vehicle->category?->category_name ?? 'Premium Fleet' }}</div>

                            <div class="vehicle-specs">
                                <span class="vehicle-spec">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                    </svg>
                                    {{ $vehicle->capacity }} seats
                                </span>
                                <span class="vehicle-spec">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M13 2L3 14h7l-1 8 10-12h-7z"></path>
                                    </svg>
                                    {{ $vehicle->transmission }}
                                </span>
                                <span class="vehicle-spec">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M14 3h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h2"></path>
                                    </svg>
                                    {{ $vehicle->fuel }}
                                </span>
                            </div>

                            <div class="vehicle-footer">
                                <div class="vehicle-price">
                                    PHP {{ number_format($vehicle->price_per_day, 0) }}
                                    <small>per day</small>
                                </div>
                                @auth
                                    <a class="book-btn" href="{{ route('customer.booking.create', ['vehicle' => $vehicle->id]) }}">Book Now</a>
                                @else
                                    <a class="book-btn" href="{{ route('login') }}?redirect={{ urlencode(route('customer.booking.create', ['vehicle' => $vehicle->id])) }}">Book Now</a>
                                @endauth
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

    </main>
    <script>
        const searchInput = document.getElementById('vehicleSearch');
        const typeFilter = document.getElementById('typeFilter');
        const transmissionFilter = document.getElementById('transmissionFilter');
        const vehicleCards = Array.from(document.querySelectorAll('.vehicle-card'));
        const vehicleCount = document.getElementById('vehicleCount');

        function formatPeso(amount) {
            return `PHP ${Number(amount).toLocaleString('en-PH', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            })}`;
        }

        function filterVehicles() {
            const query = searchInput.value.trim().toLowerCase();
            const type = typeFilter.value.trim().toLowerCase();
            const transmission = transmissionFilter.value.trim().toLowerCase();

            let visibleCount = 0;

            vehicleCards.forEach((card) => {
                const matchesQuery =
                    !query ||
                    card.dataset.name.includes(query) ||
                    card.dataset.brand.includes(query);
                const matchesType = !type || card.dataset.type === type;
                const matchesTransmission = !transmission || card.dataset.transmission === transmission;
                const visible = matchesQuery && matchesType && matchesTransmission;

                card.classList.toggle('hidden-card', !visible);
                if (visible) visibleCount += 1;
            });

            vehicleCount.textContent = visibleCount;
        }

        searchInput.addEventListener('input', filterVehicles);
        typeFilter.addEventListener('change', filterVehicles);
        transmissionFilter.addEventListener('change', filterVehicles);

        // Theme sync
        const themeBtn = document.getElementById('themeToggle');
        const body = document.body;
        const savedTheme = localStorage.getItem('theme');
        
        if (savedTheme === 'dark') {
            body.classList.add('dark-mode');
        } else if (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            body.classList.add('dark-mode');
        }

        themeBtn.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            const isDark = body.classList.contains('dark-mode');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    </script>
</body>
</html>
