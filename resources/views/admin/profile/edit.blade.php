@extends('layouts.app')
@section('title', 'My Profile')
@section('page-title', 'My Profile')
@push('styles')
<style>
    .profile-wrap{max-width:680px;margin:0 auto}
    .profile-hero{background:linear-gradient(135deg,rgba(255,107,0,.1) 0%,rgba(168,85,247,.06) 100%);border:1px solid rgba(255,107,0,.18);border-radius:20px;padding:28px;margin-bottom:24px;display:flex;align-items:center;gap:20px}
    .profile-av{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#ff8c3a,#ff6b00);display:grid;place-items:center;font-size:2rem;font-weight:900;color:#fff;flex-shrink:0;box-shadow:0 0 28px rgba(255,107,0,.35)}
    .profile-info h2{margin:0 0 4px;font-size:1.3rem;font-weight:900;letter-spacing:-.04em}
    .profile-info p{margin:0;font-size:.85rem;color:var(--muted)}
    .profile-info .role-chip{display:inline-flex;align-items:center;gap:5px;background:rgba(255,107,0,.15);border:1px solid rgba(255,107,0,.25);border-radius:999px;padding:3px 12px;font-size:.72rem;font-weight:700;color:var(--orange-l);text-transform:uppercase;letter-spacing:.05em;margin-top:8px}
    .tab-pills{display:flex;gap:8px;margin-bottom:20px}
    .tab-pill{padding:9px 20px;border-radius:12px;font-size:.88rem;font-weight:600;cursor:pointer;border:1px solid var(--line);background:var(--ghost-bg);color:var(--muted);transition:all .2s}
    .tab-pill.active{background:rgba(255,107,0,.12);border-color:rgba(255,107,0,.3);color:var(--orange-l)}
    .tab-pane{display:none}
    .tab-pane.active{display:block}
    .input-wrap{position:relative}
    .input-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--muted);pointer-events:none}
    .form-control.with-icon{padding-left:42px}
    .pw-strength{height:3px;border-radius:2px;margin-top:6px;transition:all .3s;background:var(--line)}
    .pw-strength-bar{height:100%;border-radius:2px;transition:width .4s,background .4s}
    .pw-hint{font-size:.75rem;color:var(--muted);margin-top:4px}
    .readonly-field{opacity:.55;cursor:not-allowed}
    .section-label{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:14px;display:flex;align-items:center;gap:8px}
    .section-label::after{content:'';flex:1;height:1px;background:var(--line)}
</style>
@endpush
@section('content')
<div class="profile-wrap">

    {{-- Profile Hero --}}
    <div class="profile-hero">
        <div class="profile-av">{{ strtoupper(substr($user->first_name,0,1) . substr($user->last_name,0,1)) }}</div>
        <div class="profile-info">
            <h2>{{ $user->first_name }} {{ $user->last_name }}</h2>
            <p>{{ $user->email }}</p>
            <div class="role-chip">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                {{ ucwords(str_replace('_',' ', $user->getRoleNames()->first() ?? 'Admin')) }}
            </div>
        </div>
    </div>

    @if(session('success'))
    <div style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);border-radius:10px;padding:12px 16px;color:#4ade80;margin-bottom:16px;font-size:.9rem">
        ✓ {{ session('success') }}
    </div>
    @endif

    {{-- Tabs --}}
    <div class="tab-pills">
        <button class="tab-pill active" onclick="switchTab('info', this)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:5px"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Profile Info
        </button>
        <button class="tab-pill" onclick="switchTab('security', this)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:5px"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Change Password
        </button>
    </div>

    {{-- Profile Info Tab --}}
    <div id="tab-info" class="tab-pane active">
        <div class="card">
            <form action="{{ route('admin.profile.update') }}" method="POST">
                @csrf @method('PUT')
                <div class="card-body">
                    <div class="section-label">Personal Information</div>
                    <div class="g2">
                        <div class="form-group">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}" required>
                            @error('first_name')<p style="color:var(--red);font-size:.78rem;margin-top:4px">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}" required>
                            @error('last_name')<p style="color:var(--red);font-size:.78rem;margin-top:4px">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-wrap">
                            <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,12 2,6"/></svg>
                            <input type="email" class="form-control with-icon readonly-field" value="{{ $user->email }}" readonly>
                        </div>
                        <p style="font-size:.75rem;color:var(--muted);margin-top:5px">Email cannot be changed. Contact a Super Admin if needed.</p>
                    </div>
                </div>
                <div style="padding:18px 24px;background:var(--ghost-bg);border-top:1px solid var(--line);display:flex;justify-content:flex-end">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Change Password Tab --}}
    <div id="tab-security" class="tab-pane">
        <div class="card">
            <form action="{{ route('admin.profile.update') }}" method="POST">
                @csrf @method('PUT')
                {{-- Pass current name to satisfy validation without wiping it --}}
                <input type="hidden" name="first_name" value="{{ $user->first_name }}">
                <input type="hidden" name="last_name" value="{{ $user->last_name }}">
                <div class="card-body">
                    <div class="section-label">Change Password</div>
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <div class="input-wrap">
                            <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input type="password" name="current_password" class="form-control with-icon" placeholder="Your current password" autocomplete="current-password">
                        </div>
                        @error('current_password')<p style="color:var(--red);font-size:.78rem;margin-top:4px">{{ $message }}</p>@enderror
                    </div>
                    <div class="g2">
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" id="profPw" class="form-control" placeholder="Min 8 characters" autocomplete="new-password" minlength="8">
                            <div class="pw-strength"><div class="pw-strength-bar" id="profBar" style="width:0%"></div></div>
                            <div class="pw-hint" id="profHint">Enter a new password</div>
                            @error('password')<p style="color:var(--red);font-size:.78rem;margin-top:4px">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation" id="profPwC" class="form-control" placeholder="Repeat password" autocomplete="new-password">
                            <div class="pw-hint" id="profMatchHint">&nbsp;</div>
                        </div>
                    </div>
                </div>
                <div style="padding:18px 24px;background:var(--ghost-bg);border-top:1px solid var(--line);display:flex;justify-content:flex-end">
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function switchTab(id, btn) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-pill').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + id).classList.add('active');
    btn.classList.add('active');
}

// Password strength (reusable)
function setupPwStrength(pwId, barId, hintId, matchId) {
    const pw = document.getElementById(pwId);
    const bar = document.getElementById(barId);
    const hint = document.getElementById(hintId);
    const matchHint = matchId ? document.getElementById(matchId) : null;

    if (!pw) return;
    pw.addEventListener('input', () => {
        const v = pw.value;
        let score = 0;
        if (v.length >= 8) score++;
        if (/[A-Z]/.test(v)) score++;
        if (/[0-9]/.test(v)) score++;
        if (/[^A-Za-z0-9]/.test(v)) score++;
        const colors = ['#ef4444','#f59e0b','#22c55e','#10b981'];
        const labels = ['Too short','Fair','Good','Strong'];
        const pct = [25,50,75,100];
        bar.style.width = (score > 0 ? pct[score-1] : 0) + '%';
        bar.style.background = score > 0 ? colors[score-1] : 'transparent';
        hint.textContent = score > 0 ? labels[score-1] : 'Enter a new password';
        hint.style.color = score > 0 ? colors[score-1] : 'var(--muted)';
    });

    if (matchHint) {
        const pwC = document.getElementById(matchId.replace('Match','C').replace('Hint',''));
        // use sibling input near matchHint
    }
}
setupPwStrength('profPw','profBar','profHint');

const profPwC = document.getElementById('profPwC');
const profMatchHint = document.getElementById('profMatchHint');
const profPw = document.getElementById('profPw');
if (profPwC) {
    profPwC.addEventListener('input', () => {
        if (!profPwC.value) { profMatchHint.textContent = '\u00a0'; return; }
        if (profPwC.value === profPw.value) {
            profMatchHint.textContent = '✓ Passwords match';
            profMatchHint.style.color = '#4ade80';
        } else {
            profMatchHint.textContent = '✕ Passwords do not match';
            profMatchHint.style.color = '#f87171';
        }
    });
}

// Auto-open security tab if there are password errors
@if($errors->has('password') || $errors->has('current_password'))
switchTab('security', document.querySelectorAll('.tab-pill')[1]);
@endif
</script>
@endpush
