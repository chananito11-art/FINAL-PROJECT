@extends('layouts.customer')

@section('title', 'Edit Profile')
@section('title_display', 'Profile Settings')

@push('styles')
<style>
    .prof-wrap { max-width: 650px; margin: 40px auto; padding: 0 24px 80px; }
    .card { background: var(--card-bg); border: 1px solid var(--line); border-radius: 24px; padding: 32px; }
    
    .form-section { margin-bottom: 32px; }
    .section-title { font-size: .8rem; font-weight: 800; color: var(--muted); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 20px; border-bottom: 1px solid var(--line); padding-bottom: 8px; }
    
    .g2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .form-group { margin-bottom: 20px; }
    .label { display: block; font-size: .85rem; font-weight: 700; color: var(--muted); margin-bottom: 8px; }
    .control { width: 100%; height: 46px; background: var(--input-bg); border: 1px solid var(--line); border-radius: 12px; color: var(--text); padding: 0 16px; font-family: inherit; font-size: .95rem; outline: none; transition: all .2s; }
    .control:focus { border-color: var(--orange); box-shadow: 0 0 0 4px rgba(255,107,0,.1); }
    
    .error { color: var(--red); font-size: .78rem; margin-top: 6px; font-weight: 600; }
    
    @media (max-width: 500px) { .g2 { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="prof-wrap" style="max-width: 650px; margin: 40px auto; padding: 0 24px 80px;">
    <form action="{{ route('customer.profile.update') }}" method="POST" class="card" style="background: var(--card-bg); border: 1px solid var(--line); border-radius: 24px; padding: 32px;">
        @csrf
        @method('PUT')

        <div class="form-section" style="margin-bottom: 32px;">
            <div class="section-title" style="font-size: .8rem; font-weight: 800; color: var(--muted); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 20px; border-bottom: 1px solid var(--line); padding-bottom: 8px;">Personal Information</div>
            <div class="g2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}" required>
                    @error('first_name')<div class="error" style="color: var(--red); font-size: .78rem; margin-top: 6px; font-weight: 600;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}" required>
                    @error('last_name')<div class="error" style="color: var(--red); font-size: .78rem; margin-top: 6px; font-weight: 600;">{{ $message }}</div>@enderror
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Email Address (Cannot be changed)</label>
                <input type="email" class="form-control" value="{{ $user->email }}" readonly style="opacity: 0.6; cursor: not-allowed; background: var(--dark2)">
            </div>
            
            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="e.g. 09123456789">
                @error('phone')<div class="error" style="color: var(--red); font-size: .78rem; margin-top: 6px; font-weight: 600;">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-section" style="margin-bottom: 32px;">
            <div class="section-title" style="font-size: .8rem; font-weight: 800; color: var(--muted); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 20px; border-bottom: 1px solid var(--line); padding-bottom: 8px;">Identity & Verification</div>
            <div style="background: var(--hover-bg); border: 1px solid var(--line); border-radius: 16px; padding: 24px; margin-bottom: 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <span style="font-size: .9rem; font-weight: 700; color: var(--muted);">Verification Status</span>
                    @php
                        $statusColors = [
                            'verified' => ['bg' => 'rgba(34,197,94,.15)', 'c' => '#4ade80', 'text' => 'Verified'],
                            'pending'  => ['bg' => 'rgba(59,130,246,.15)', 'c' => '#60a5fa', 'text' => 'Pending Review'],
                            'rejected' => ['bg' => 'rgba(239,68,68,.15)', 'c' => '#f87171', 'text' => 'Rejected'],
                            'expired'  => ['bg' => 'rgba(239,68,68,.15)', 'c' => '#f87171', 'text' => 'Expired'],
                        ];
                        $s = $statusColors[$user->verification_status] ?? ['bg' => 'rgba(255,255,255,.08)', 'c' => 'var(--muted)', 'text' => 'Unverified'];
                    @endphp
                    <span style="font-size: .75rem; font-weight: 800; padding: 5px 14px; border-radius: 20px; text-transform: uppercase; background: {{ $s['bg'] }}; color: {{ $s['c'] }}; letter-spacing: 0.05em;">
                        {{ $s['text'] }}
                    </span>
                </div>

                @if($user->id_expiration_date)
                <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 16px; border-top: 1px solid var(--line);">
                    <div>
                        <span style="display: block; font-size: .9rem; font-weight: 700; color: var(--muted);">Driver's License Expiry</span>
                        <span style="font-size: .8rem; color: var(--muted);">Linked to your verified identity</span>
                    </div>
                    <div style="text-align: right;">
                        <span style="display: block; font-size: 1.05rem; font-weight: 800; color: {{ $user->id_expiration_date->isPast() ? 'var(--red)' : 'var(--text)' }};">
                            {{ $user->id_expiration_date->format('M d, Y') }}
                        </span>
                        @if($user->id_expiration_date->isPast())
                            <span style="font-size: .72rem; color: var(--red); font-weight: 800; text-transform: uppercase;">Expired</span>
                        @else
                            <span style="font-size: .72rem; color: var(--green); font-weight: 800; text-transform: uppercase;">Valid</span>
                        @endif
                    </div>
                </div>

                @if($user->verification_status === 'expired' || $user->verification_status === 'rejected' || $user->id_expiration_date->isPast())
                <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--line); text-align: center;">
                    <a href="{{ route('customer.verification.show') }}" class="btn btn-ghost" style="width: auto; padding: 0 20px; font-size: .85rem; color: var(--orange);">Update Verification Document →</a>
                </div>
                @elseif($user->verification_status === 'pending')
                <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--line); text-align: center;">
                    <p style="font-size: .85rem; color: var(--muted); font-style: italic;">Verification is currently in progress...</p>
                </div>
                @endif
                @else
                <div style="padding-top: 16px; border-top: 1px solid var(--line); text-align: center;">
                    <p style="font-size: .88rem; color: var(--muted); margin-bottom: 14px;">No driver's license information on file.</p>
                    <a href="{{ route('customer.verification.show') }}" class="btn btn-ghost" style="width: auto; padding: 0 20px; font-size: .85rem;">Verify Your Identity →</a>
                </div>
                @endif
            </div>
        </div>

        <div class="form-section" style="margin-bottom: 20px">
            <div class="section-title" style="font-size: .8rem; font-weight: 800; color: var(--muted); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 20px; border-bottom: 1px solid var(--line); padding-bottom: 8px;">Security</div>
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                @error('password')<div class="error" style="color: var(--red); font-size: .78rem; margin-top: 6px; font-weight: 600;">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; height: 52px; font-size: 1rem; border-radius: 14px">
            Save Changes
        </button>
    </form>
</div>
@endsection
