<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — OrangeCrush Car Rentals</title>
    <meta name="description" content="Sign in to OrangeCrush Car Rentals to book your perfect vehicle.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --orange:      #ff6b00;
            --orange-light:#ff8c3a;
            --orange-glow: rgba(255, 107, 0, 0.35);
            --dark:        #06091b;
            --dark-2:      #0d1128;
            --dark-3:      #141932;
            --dark-4:      #1c2240;
            --text:        #f0f2ff;
            --text-muted:  rgba(240, 242, 255, 0.55);
            --line:        rgba(255, 255, 255, 0.08);
            --radius-card: 24px;
            --radius-input:14px;
            --transition:  0.25s ease;
            --card-bg:     rgba(255, 255, 255, 0.04);
            --input-bg:    rgba(255, 255, 255, 0.06);
            --grid-color:  rgba(255, 255, 255, 0.045);
            --shadow:      rgba(0, 0, 0, 0.5);
            --placeholder: rgba(240, 242, 255, 0.3);
            --focus-bg:    rgba(255, 107, 0, 0.06);
        }

        body.light-mode {
            --dark:        #f3f4f6;
            --dark-2:      #ffffff;
            --text:        #111827;
            --text-muted:  rgba(17, 24, 39, 0.6);
            --line:        rgba(0, 0, 0, 0.1);
            --card-bg:     #ffffff;
            --input-bg:    #ffffff;
            --grid-color:  rgba(0, 0, 0, 0.045);
            --orange-glow: rgba(255, 107, 0, 0.15);
            --shadow:      rgba(0, 0, 0, 0.05);
            --placeholder: rgba(17, 24, 39, 0.4);
            --focus-bg:    #ffffff;
        }

        html, body {
            height: 100%;
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--dark);
            color: var(--text);
        }

        /* ── Animated background ── */
        .bg-scene {
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 0%,  rgba(255, 107, 0, 0.18) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 100%, rgba(255, 107, 0, 0.12) 0%, transparent 55%),
                linear-gradient(160deg, var(--dark) 0%, var(--dark-2) 50%, var(--dark) 100%);
            z-index: 0;
        }

        /* subtle moving orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.55;
            animation: drift 12s ease-in-out infinite alternate;
        }
        .orb-1 {
            width: 420px; height: 420px;
            background: radial-gradient(circle, rgba(255,107,0,0.4) 0%, transparent 70%);
            top: -120px; left: -80px;
            animation-delay: 0s;
        }
        .orb-2 {
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(255,140,58,0.3) 0%, transparent 70%);
            bottom: -60px; right: -60px;
            animation-delay: -6s;
        }

        @keyframes drift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(30px, 20px) scale(1.08); }
        }

        /* ── Grid dots overlay ── */
        .bg-scene::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, var(--grid-color) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        /* ── Layout ── */
        .page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        /* ── Left panel — branding ── */
        .brand-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 64px;
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 56px;
        }

        .brand-logo-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--orange-light), var(--orange));
            border-radius: 14px;
            display: grid;
            place-items: center;
            box-shadow: 0 0 24px var(--orange-glow);
        }

        .brand-logo-name {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--text);
        }

        .brand-panel h1 {
            font-size: clamp(2.2rem, 4vw, 3.2rem);
            font-weight: 900;
            letter-spacing: -0.05em;
            line-height: 1.1;
            margin-bottom: 20px;
        }

        .brand-panel h1 span {
            background: linear-gradient(90deg, var(--orange-light), var(--orange));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-panel p {
            font-size: 1.05rem;
            color: var(--text-muted);
            line-height: 1.65;
            max-width: 420px;
            margin-bottom: 48px;
        }

        .feature-list {
            display: grid;
            gap: 16px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 0.97rem;
            color: var(--text-muted);
        }

        .feature-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--orange-light), var(--orange));
            flex-shrink: 0;
            box-shadow: 0 0 8px var(--orange-glow);
        }

        /* ── Right panel — form ── */
        .form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
        }

        .card {
            width: 100%;
            max-width: 440px;
            background: var(--card-bg);
            border: 1px solid var(--line);
            border-radius: var(--radius-card);
            padding: 44px 40px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow:
                0 0 0 1px var(--line) inset,
                0 32px 64px var(--shadow);
        }

        .card-header {
            margin-bottom: 36px;
        }

        .card-header h2 {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.04em;
            margin-bottom: 8px;
        }

        .card-header p {
            font-size: 0.95rem;
            color: var(--text-muted);
        }

        /* ── Alert errors ── */
        .alert-error {
            background: rgba(255, 60, 60, 0.12);
            border: 1px solid rgba(255, 60, 60, 0.25);
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.9rem;
            color: #ff8080;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-info {
            background: rgba(255, 107, 0, 0.1);
            border: 1px solid rgba(255, 107, 0, 0.22);
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.9rem;
            color: var(--orange-light);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ── Form fields ── */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            height: 52px;
            background: var(--input-bg);
            border: 1px solid var(--line);
            border-radius: var(--radius-input);
            color: var(--text);
            font-family: inherit;
            font-size: 1rem;
            padding: 0 16px 0 46px;
            transition: border-color var(--transition), background var(--transition), box-shadow var(--transition);
            outline: none;
            -webkit-appearance: none;
        }

        .form-control::placeholder {
            color: var(--placeholder);
        }

        .form-control:focus {
            border-color: rgba(255, 107, 0, 0.6);
            background: var(--focus-bg);
            box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.15);
        }

        .form-control.is-error {
            border-color: rgba(255, 80, 80, 0.6);
        }

        .error-msg {
            margin-top: 6px;
            font-size: 0.83rem;
            color: #ff8080;
        }

        /* ── Password toggle ── */
        .pw-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            padding: 4px;
            transition: color var(--transition);
        }

        .pw-toggle:hover {
            color: var(--text);
        }

        .pw-field .form-control {
            padding-right: 48px;
        }

        /* ── Remember row ── */
        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.92rem;
            color: var(--text-muted);
            cursor: pointer;
            user-select: none;
        }

        .remember-label input[type="checkbox"] {
            accent-color: var(--orange);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .forgot-link {
            font-size: 0.88rem;
            color: var(--orange-light);
            text-decoration: none;
            transition: color var(--transition);
        }

        .forgot-link:hover {
            color: var(--orange);
            text-decoration: underline;
        }

        /* ── Submit button ── */
        .btn-primary {
            width: 100%;
            height: 52px;
            background: linear-gradient(135deg, var(--orange-light) 0%, var(--orange) 100%);
            border: none;
            border-radius: var(--radius-input);
            color: #fff;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.01em;
            transition: transform var(--transition), box-shadow var(--transition), filter var(--transition);
            box-shadow: 0 4px 20px var(--orange-glow);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 60%);
            opacity: 0;
            transition: opacity var(--transition);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px var(--orange-glow);
            filter: brightness(1.08);
        }

        .btn-primary:hover::after {
            opacity: 1;
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* ── Divider ── */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 28px 0;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--line);
        }

        /* ── Role pills (demo hint) ── */
        .role-hint {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 16px;
        }

        .role-hint-title {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            margin-bottom: 12px;
        }

        .role-pills {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .role-pill {
            background: rgba(255, 107, 0, 0.08);
            border: 1px solid rgba(255, 107, 0, 0.18);
            border-radius: 10px;
            padding: 10px 12px;
            cursor: pointer;
            transition: background var(--transition), border-color var(--transition);
        }

        .role-pill:hover {
            background: rgba(255, 107, 0, 0.16);
            border-color: rgba(255, 107, 0, 0.35);
        }

        .role-pill-name {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--orange-light);
            margin-bottom: 2px;
        }

        .role-pill-email {
            font-size: 0.78rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── Back to home link ── */
        .back-link {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.88rem;
            color: var(--text-muted);
            text-decoration: none;
            margin-top: 28px;
            justify-content: center;
            transition: color var(--transition);
        }

        .back-link:hover {
            color: var(--text);
        }

        /* ── Responsive ── */
        @media (max-width: 860px) {
            .page {
                grid-template-columns: 1fr;
            }

            .brand-panel {
                display: none;
            }

            .form-panel {
                padding: 32px 16px;
            }
        }
    </style>
</head>
<body>

<div class="bg-scene">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
</div>

<div class="page">
    <!-- ── Left Branding Panel ── -->
    <div class="brand-panel">
        <div class="brand-logo">
            <div class="brand-logo-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 13l1.5-4.5A3 3 0 0 1 7.35 6h9.3a3 3 0 0 1 2.85 2.5L21 13"/>
                    <path d="M5 16h14"/><path d="M6 16v2a1 1 0 0 0 1 1h1"/>
                    <path d="M16 19h1a1 1 0 0 0 1-1v-2"/>
                    <circle cx="7.5" cy="15.5" r="1.5"/><circle cx="16.5" cy="15.5" r="1.5"/>
                </svg>
            </div>
            <span class="brand-logo-name">OrangeCrush</span>
        </div>

        <h1>Your journey<br>starts <span>here.</span></h1>
        <p>Access the OrangeCrush Car Rentals platform to browse vehicles, manage bookings, and drive your business forward.</p>


    </div>

    <!-- ── Right Form Panel ── -->
    <div class="form-panel">
        <div class="card">
            <div class="card-header">
                <!-- Mobile logo -->
                <div class="brand-logo" style="display:none; margin-bottom:24px;" id="mobileLogo">
                    <div class="brand-logo-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 13l1.5-4.5A3 3 0 0 1 7.35 6h9.3a3 3 0 0 1 2.85 2.5L21 13"/>
                            <path d="M5 16h14"/><path d="M6 16v2a1 1 0 0 0 1 1h1"/>
                            <path d="M16 19h1a1 1 0 0 0 1-1v-2"/>
                            <circle cx="7.5" cy="15.5" r="1.5"/><circle cx="16.5" cy="15.5" r="1.5"/>
                        </svg>
                    </div>
                    <span class="brand-logo-name">OrangeCrush</span>
                </div>
                <h2>Welcome back</h2>
                <p>Sign in to your account to continue</p>
            </div>

            {{-- Session / validation errors --}}
            @if (session('error'))
                <div class="alert-error" role="alert">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->has('email'))
                <div class="alert-error" role="alert">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    {{ $errors->first('email') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                @csrf

                {{-- Email --}}
                <div class="form-group">
                    <label class="form-label" for="email">Email address</label>
                    <div class="input-wrap">
                        <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control @error('email') is-error @enderror"
                            placeholder="you@example.com"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                        >
                    </div>
                </div>

                {{-- Password --}}
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrap pw-field">
                        <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="pw-toggle" id="pwToggle" aria-label="Toggle password visibility">
                            <svg id="eyeShow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="eyeHide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <div class="remember-row">
                    <label class="remember-label" for="remember">
                        <input type="checkbox" id="remember" name="remember">
                        Remember me
                    </label>
                    <a href="#" class="forgot-link">Forgot Password?</a>
                </div>

                <button type="submit" class="btn-primary" id="submitBtn">
                    Sign In
                </button>
            </form>


            <a href="/" class="back-link" id="backToHome">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                Back to homepage
            </a>
        </div>
    </div>
</div>

<script>
    // ── Show mobile logo on small screens
    const ml = document.getElementById('mobileLogo');
    function checkMobile() {
        ml.style.display = window.innerWidth <= 860 ? 'inline-flex' : 'none';
    }
    checkMobile();
    window.addEventListener('resize', checkMobile);

    // ── Password visibility toggle
    const pwToggle = document.getElementById('pwToggle');
    const pwInput  = document.getElementById('password');
    const eyeShow  = document.getElementById('eyeShow');
    const eyeHide  = document.getElementById('eyeHide');

    pwToggle.addEventListener('click', function () {
        const isHidden = pwInput.type === 'password';
        pwInput.type   = isHidden ? 'text' : 'password';
        eyeShow.style.display = isHidden ? 'none'  : 'block';
        eyeHide.style.display = isHidden ? 'block' : 'none';
    });


    // ── Button loading state on submit
    const form      = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function () {
        submitBtn.disabled    = true;
        submitBtn.textContent = 'Signing in…';
    });

    // Theme sync
    if (localStorage.getItem('theme') === 'light' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: light)').matches)) {
        document.body.classList.add('light-mode');
    }
</script>
</body>
</html>
