<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — OrangeCrush Car Rentals</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--orange:#ff6b00;--orange-l:#ff8c3a;--og:rgba(255,107,0,.35);--dark:#06091b;--text:#f0f2ff;--muted:rgba(240,242,255,.55);--line:rgba(255,255,255,.08);--t:.25s ease;--card-bg:rgba(255,255,255,.04);--input-bg:rgba(255,255,255,.06);--shadow:rgba(0,0,0,.5);--dark-2:#0d1128;}
        @media (prefers-color-scheme: light) {
            :root {
                --dark: #f3f4f6;
                --dark-2: #ffffff;
                --text: #111827;
                --muted: rgba(17,24,39,0.6);
                --line: rgba(0,0,0,0.1);
                --card-bg: #ffffff;
                --input-bg: #f9fafb;
                --og: rgba(255,107,0,0.15);
                --shadow: rgba(0,0,0,0.05);
            }
        }
        html,body{height:100%;font-family:'Inter',system-ui,sans-serif;background:var(--dark);color:var(--text)}
        .bg{position:fixed;inset:0;background:radial-gradient(ellipse 80% 60% at 20% 0%,rgba(255,107,0,.18) 0%,transparent 60%),radial-gradient(ellipse 60% 50% at 80% 100%,rgba(255,107,0,.12) 0%,transparent 55%),linear-gradient(160deg,var(--dark) 0%,var(--dark-2) 50%,var(--dark) 100%);z-index:0}
        .orb{position:absolute;border-radius:50%;filter:blur(80px);opacity:.5;animation:drift 12s ease-in-out infinite alternate}
        .orb1{width:400px;height:400px;background:radial-gradient(circle,rgba(255,107,0,.4) 0%,transparent 70%);top:-100px;left:-80px}
        .orb2{width:280px;height:280px;background:radial-gradient(circle,rgba(255,140,58,.3) 0%,transparent 70%);bottom:-60px;right:-60px;animation-delay:-6s}
        @keyframes drift{from{transform:translate(0,0) scale(1)}to{transform:translate(30px,20px) scale(1.08)}}
        .page{position:relative;z-index:1;min-height:100vh;display:grid;grid-template-columns:1fr 1fr}
        .brand-panel{display:flex;flex-direction:column;justify-content:center;padding:64px}
        .brand-logo{display:inline-flex;align-items:center;gap:12px;margin-bottom:48px;text-decoration:none}
        .brand-icon{width:44px;height:44px;background:linear-gradient(135deg,var(--orange-l),var(--orange));border-radius:14px;display:grid;place-items:center;box-shadow:0 0 24px var(--og)}
        .brand-name{font-size:1.25rem;font-weight:800;letter-spacing:-.03em;color:var(--text)}
        .brand-panel h1{font-size:clamp(2rem,4vw,3rem);font-weight:900;letter-spacing:-.05em;line-height:1.1;margin-bottom:18px}
        .brand-panel h1 span{background:linear-gradient(90deg,var(--orange-l),var(--orange));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .brand-panel p{font-size:1rem;color:var(--muted);line-height:1.65;max-width:400px}
        .form-panel{display:flex;align-items:center;justify-content:center;padding:40px 24px}
        .card{width:100%;max-width:480px;background:var(--card-bg);border:1px solid var(--line);border-radius:24px;padding:40px 38px;backdrop-filter:blur(20px);box-shadow:0 0 0 1px var(--line) inset,0 32px 64px var(--shadow)}
        .card h2{font-size:1.6rem;font-weight:800;letter-spacing:-.04em;margin-bottom:6px}
        .card>p{font-size:.9rem;color:var(--muted);margin-bottom:28px}
        .g2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
        .form-group{margin-bottom:16px}
        .form-label{display:block;font-size:.82rem;font-weight:600;color:var(--muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.03em}
        .input-wrap{position:relative}
        .input-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--muted);pointer-events:none}
        .form-control{width:100%;height:48px;background:var(--input-bg);border:1px solid var(--line);border-radius:12px;color:var(--text);font-family:inherit;font-size:.95rem;padding:0 14px 0 42px;outline:none;transition:border-color var(--t),box-shadow var(--t)}
        .form-control::placeholder{color:rgba(240,242,255,.28)}
        .form-control:focus{border-color:rgba(255,107,0,.6);background:rgba(255,107,0,.06);box-shadow:0 0 0 3px rgba(255,107,0,.15)}
        .form-control.no-icon{padding-left:14px}
        .error-msg{margin-top:5px;font-size:.8rem;color:#ff8080}
        .alert-error{background:rgba(255,60,60,.12);border:1px solid rgba(255,60,60,.25);border-radius:10px;padding:11px 15px;font-size:.88rem;color:#ff8080;margin-bottom:20px}
        .btn-primary{width:100%;height:50px;background:linear-gradient(135deg,var(--orange-l),var(--orange));border:none;border-radius:12px;color:#fff;font-family:inherit;font-size:1rem;font-weight:700;cursor:pointer;box-shadow:0 4px 20px var(--og);transition:filter var(--t),transform var(--t)}
        .btn-primary:hover{filter:brightness(1.08);transform:translateY(-2px)}
        .login-link{text-align:center;margin-top:22px;font-size:.9rem;color:var(--muted)}
        .login-link a{color:var(--orange-l);text-decoration:none;font-weight:600}
        .login-link a:hover{text-decoration:underline}
        @media(max-width:860px){.page{grid-template-columns:1fr}.brand-panel{display:none}}
    </style>
</head>
<body>
<div class="bg"><div class="orb orb1"></div><div class="orb orb2"></div></div>
<div class="page">
    <div class="brand-panel">
        <a href="/" class="brand-logo">
            <div class="brand-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M3 13l1.5-4.5A3 3 0 0 1 7.35 6h9.3a3 3 0 0 1 2.85 2.5L21 13"/><path d="M5 16h14"/><circle cx="7.5" cy="15.5" r="1.5"/><circle cx="16.5" cy="15.5" r="1.5"/></svg>
            </div>
            <span class="brand-name">OrangeCrush</span>
        </a>
        <h1>Start your<br><span>journey today.</span></h1>
        <p>Create a free account to browse our premium fleet, make bookings, and track your rentals — all in one place.</p>
    </div>
    <div class="form-panel">
        <div class="card">
            <h2>Create account</h2>
            <p>Join OrangeCrush Car Rentals</p>
            @if($errors->any())
                <div class="alert-error">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('register') }}" novalidate>
                @csrf
                <div class="g2">
                    <div class="form-group">
                        <label class="form-label" for="first_name">First Name</label>
                        <div class="input-wrap">
                            <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <input type="text" id="first_name" name="first_name" class="form-control @error('first_name') is-error @enderror" placeholder="Juan" value="{{ old('first_name') }}" required>
                        </div>
                        @error('first_name')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="last_name">Last Name</label>
                        <div class="input-wrap">
                            <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <input type="text" id="last_name" name="last_name" class="form-control @error('last_name') is-error @enderror" placeholder="Dela Cruz" value="{{ old('last_name') }}" required>
                        </div>
                        @error('last_name')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="input-wrap">
                        <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-error @enderror" placeholder="you@example.com" value="{{ old('email') }}" required autocomplete="email">
                    </div>
                    @error('email')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <div class="input-wrap">
                        <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.63 3.36 2 2 0 0 1 3.6 1.18h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.95a16 16 0 0 0 6.29 6.29l.61-.61a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.73 17z"/></svg>
                        <input type="text" id="phone" name="phone" class="form-control" placeholder="09XXXXXXXXX" value="{{ old('phone') }}">
                    </div>
                </div>
                <div class="g2">
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-wrap">
                            <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Min 8 chars" required autocomplete="new-password">
                        </div>
                        @error('password')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm</label>
                        <div class="input-wrap">
                            <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Repeat" required autocomplete="new-password">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-primary" id="submitBtn">Create Account</button>
            </form>
            <p class="login-link">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
        </div>
    </div>
</div>
<script>
document.querySelector('form').addEventListener('submit',function(){
    var b=document.getElementById('submitBtn');b.disabled=true;b.textContent='Creating account…';
});
</script>
</body>
</html>
