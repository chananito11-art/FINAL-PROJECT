@extends('layouts.app')
@section('title','Add Employee')
@section('page-title','Add Employee')
@push('styles')
<style>
    .emp-wrap{max-width:640px;margin:0 auto}
    .emp-hero{background:linear-gradient(135deg,rgba(255,107,0,.12) 0%,rgba(255,140,58,.06) 100%);border:1px solid rgba(255,107,0,.2);border-radius:20px;padding:28px;margin-bottom:24px;display:flex;align-items:center;gap:20px}
    .emp-avatar{width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#ff8c3a,#ff6b00);display:grid;place-items:center;font-size:1.8rem;font-weight:900;color:#fff;flex-shrink:0;box-shadow:0 0 24px rgba(255,107,0,.35);transition:all .3s}
    .emp-hero-text h2{margin:0 0 4px;font-size:1.2rem;font-weight:800;letter-spacing:-.03em}
    .emp-hero-text p{margin:0;font-size:.85rem;color:var(--muted)}
    .form-section{margin-bottom:20px}
    .section-label{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:14px;display:flex;align-items:center;gap:8px}
    .section-label::after{content:'';flex:1;height:1px;background:var(--line)}
    .input-wrap{position:relative}
    .input-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--muted);pointer-events:none}
    .form-control.with-icon{padding-left:42px}
    .pw-strength{height:3px;border-radius:2px;margin-top:6px;transition:all .3s;background:var(--line)}
    .pw-strength-bar{height:100%;border-radius:2px;transition:width .4s,background .4s}
    .pw-hint{font-size:.75rem;color:var(--muted);margin-top:4px}
    .role-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .role-option{position:relative}
    .role-option input{position:absolute;opacity:0;width:0;height:0}
    .role-card{padding:14px 16px;border:2px solid var(--line);border-radius:14px;cursor:pointer;transition:all .2s;background:var(--ghost-bg)}
    .role-card:hover{border-color:rgba(255,107,0,.3);background:rgba(255,107,0,.04)}
    .role-option input:checked + .role-card{border-color:#ff6b00;background:rgba(255,107,0,.08)}
    .role-name{font-weight:700;font-size:.88rem;margin-bottom:2px}
    .role-desc{font-size:.75rem;color:var(--muted)}
    .submit-row{display:flex;gap:10px;align-items:center;justify-content:flex-end;padding:20px 24px;background:var(--ghost-bg);border-top:1px solid var(--line);border-radius:0 0 18px 18px;margin:-1px}
</style>
@endpush
@section('content')
<div class="emp-wrap">
    <div style="margin-bottom:20px">
        <a href="{{ route('admin.users.index') }}?tab=employees" class="btn btn-ghost btn-sm">← Back to Users</a>
    </div>

    <div class="emp-hero">
        <div class="emp-avatar" id="empAvatar">?</div>
        <div class="emp-hero-text">
            <h2 id="empFullName">New Employee</h2>
            <p>Fill in the details below to create a new staff account.</p>
        </div>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('admin.users.employees.store') }}">
            @csrf
            @if($errors->any())
            <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);border-radius:10px;padding:12px 16px;color:#f87171;margin:20px 24px 0;font-size:.88rem">
                {{ $errors->first() }}
            </div>
            @endif

            <div class="card-body">
                {{-- Personal Info --}}
                <div class="form-section">
                    <div class="section-label">Personal Information</div>
                    <div class="g2">
                        <div class="form-group">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" id="empFirst" class="form-control" value="{{ old('first_name') }}" required autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" id="empLast" class="form-control" value="{{ old('last_name') }}" required autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-wrap">
                            <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,12 2,6"/></svg>
                            <input type="email" name="email" class="form-control with-icon" value="{{ old('email') }}" required placeholder="staff@example.com">
                        </div>
                    </div>
                </div>

                {{-- Role --}}
                <div class="form-section">
                    <div class="section-label">Role & Permissions</div>
                    <div class="role-grid">
                        @php
                        $roleDescriptions = [
                            'admin'       => 'Full access to all modules',
                            'staff'       => 'Bookings, payments, inspections',
                            'mechanic'    => 'Maintenance & PM checklists',
                            'receptionist'=> 'Walk-ins and customer service',
                        ];
                        @endphp
                        @foreach($roles as $role)
                        <label class="role-option">
                            <input type="radio" name="role" value="{{ $role->name }}" {{ old('role') === $role->name ? 'checked' : '' }} required>
                            <div class="role-card">
                                <div class="role-name">{{ ucwords(str_replace('_',' ', $role->name)) }}</div>
                                <div class="role-desc">{{ $roleDescriptions[$role->name] ?? 'System role' }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Password --}}
                <div class="form-section">
                    <div class="section-label">Security</div>
                    <div class="g2">
                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" id="empPw" class="form-control" required minlength="8" autocomplete="new-password">
                            <div class="pw-strength"><div class="pw-strength-bar" id="pwBar" style="width:0%"></div></div>
                            <div class="pw-hint" id="pwHint">Enter a password</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="empPwC" class="form-control" required autocomplete="new-password">
                            <div class="pw-hint" id="pwMatchHint">&nbsp;</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="submit-row">
                <a href="{{ route('admin.users.index') }}?tab=employees" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:6px"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                    Create Employee
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
const first = document.getElementById('empFirst');
const last  = document.getElementById('empLast');
const avatar = document.getElementById('empAvatar');
const nameLabel = document.getElementById('empFullName');

function updateAvatar() {
    const f = first.value.trim(), l = last.value.trim();
    const initials = (f[0] || '') + (l[0] || '');
    avatar.textContent = initials ? initials.toUpperCase() : '?';
    nameLabel.textContent = [f,l].filter(Boolean).join(' ') || 'New Employee';
}
first.addEventListener('input', updateAvatar);
last.addEventListener('input', updateAvatar);

// Password strength
const pw = document.getElementById('empPw');
const pwC = document.getElementById('empPwC');
const pwBar = document.getElementById('pwBar');
const pwHint = document.getElementById('pwHint');
const pwMatchHint = document.getElementById('pwMatchHint');

pw.addEventListener('input', () => {
    const v = pw.value;
    let score = 0;
    if (v.length >= 8) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    const colors = ['#ef4444','#f59e0b','#22c55e','#10b981'];
    const labels = ['Too short','Fair','Good','Strong'];
    const pct    = [25,50,75,100];
    pwBar.style.width    = (score > 0 ? pct[score-1] : 0) + '%';
    pwBar.style.background = score > 0 ? colors[score-1] : 'transparent';
    pwHint.textContent   = score > 0 ? labels[score-1] : 'Enter a password';
    pwHint.style.color   = score > 0 ? colors[score-1] : 'var(--muted)';
});

pwC.addEventListener('input', () => {
    if (!pwC.value) { pwMatchHint.textContent = '\u00a0'; return; }
    if (pwC.value === pw.value) {
        pwMatchHint.textContent = '✓ Passwords match';
        pwMatchHint.style.color = '#4ade80';
    } else {
        pwMatchHint.textContent = '✕ Passwords do not match';
        pwMatchHint.style.color = '#f87171';
    }
});
</script>
@endpush
