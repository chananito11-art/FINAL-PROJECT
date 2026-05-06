@extends('layouts.customer')
@section('title','My Bookings')
@push('styles')
<style>
    .track-wrap{max-width:900px;margin:40px auto;padding:0 20px 80px}
    .track-wrap h1{font-size:1.6rem;font-weight:900;letter-spacing:-.04em;margin-bottom:4px}
    .track-wrap>.sub{color:var(--muted);margin-bottom:28px}
    .booking-card{background:var(--card-bg);border:1px solid var(--line);border-radius:16px;padding:20px 22px;margin-bottom:14px;display:flex;align-items:center;gap:18px;text-decoration:none;color:inherit;transition:background .2s,border-color .2s}
    .booking-card:hover{background:var(--hover-bg);border-color:rgba(255,107,0,.2)}
    .bc-img{width:80px;height:60px;border-radius:10px;object-fit:cover;flex-shrink:0;background:var(--dark2)}
    .bc-info{flex:1}
    .bc-name{font-size:.98rem;font-weight:700;margin-bottom:4px}
    .bc-dates{font-size:.83rem;color:var(--text-dim)}
    .bc-right{display:flex;flex-direction:column;align-items:flex-end;gap:8px}
    .bc-price{font-size:1rem;font-weight:800;color:#ff8c3a}
    .badge{display:inline-flex;padding:4px 11px;border-radius:999px;font-size:.75rem;font-weight:700}
    .s-pending_payment{background:rgba(245,158,11,.15);color:#fbbf24}
    .s-awaiting_verification{background:rgba(59,130,246,.15);color:#60a5fa}
    .s-confirmed{background:rgba(34,197,94,.15);color:#4ade80}
    .s-rejected{background:rgba(239,68,68,.15);color:#f87171}
    .s-ongoing{background:rgba(255,107,0,.15);color:#ff8c3a}
    .s-completed{background:var(--badge-y);color:var(--muted)}
    .s-cancelled{background:rgba(239,68,68,.12);color:#f87171}
    .empty{text-align:center;padding:80px 20px;color:var(--text-dim)}
    .empty a{color:#ff8c3a;text-decoration:none;font-weight:600}
</style>
@endpush
@section('content')
<div class="track-wrap">
    <h1>My Bookings</h1>
    <p class="sub">Track all your rental bookings</p>
    @forelse($bookings as $booking)
    <a href="{{ route('customer.tracking.show', $booking) }}" class="booking-card">
        <img class="bc-img" src="{{ $booking->vehicle->image_url }}" alt="{{ $booking->vehicle->name }}">
        <div class="bc-info">
            <div class="bc-name">{{ $booking->vehicle->name }}</div>
            <div class="bc-dates">
                {{ $booking->pickup_date->format('M d') }} → {{ $booking->return_date->format('M d, Y') }}
                · {{ $booking->duration_in_days }} day(s)
            </div>
        </div>
        <div class="bc-right">
            <span class="badge s-{{ $booking->status }}">{{ ucwords(str_replace('_',' ',$booking->status)) }}</span>
            <span class="bc-price">PHP {{ number_format($booking->total_amount,0) }}</span>
        </div>
    </a>
    @empty
    <div class="empty">
        <p style="font-size:1.1rem;margin-bottom:12px">No bookings yet.</p>
        <p><a href="{{ route('vehicles.index') }}">Browse available vehicles →</a></p>
    </div>
    @endforelse
</div>
@endsection
