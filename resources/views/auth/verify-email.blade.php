@extends('layouts.app')
@section('title', 'Verify Email')
@section('content')
<div style="display:flex; justify-content:center; align-items:center; min-height:60vh">
    <div class="card" style="max-width:480px; width:100%; text-align:center; padding:40px 20px">
        <div style="width:64px; height:64px; background:rgba(255,107,0,.1); border-radius:16px; display:grid; place-items:center; margin:0 auto 24px">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        </div>
        <h2 style="margin-bottom:12px">Verify Your Email</h2>
        <p style="color:var(--muted); line-height:1.6; margin-bottom:32px">
            Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? 
            <br><br>
            <span style="font-size:0.85rem; font-style:italic">Local development tip: Check <strong>storage/logs/laravel.log</strong> to find the verification link.</span>
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success" style="margin-bottom:24px">
                A new verification link has been sent to the email address you provided during registration.
            </div>
        @endif

        <div style="display:flex; flex-direction:column; gap:12px">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary" style="width:100%">Resend Verification Email</button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost" style="width:100%">Log Out</button>
            </form>
        </div>
    </div>
</div>
@endsection
