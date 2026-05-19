@extends('layouts.customer')

@section('title', 'My Payments')
@section('title_display', 'Payments')

@push('styles')
<style>
    .wrap { max-width: 900px; margin: 40px auto; padding: 0 24px 80px; }
    .card { background: var(--card-bg); border: 1px solid var(--line); border-radius: 20px; overflow: hidden; margin-bottom: 24px; }
    .card-header { padding: 20px 24px; border-bottom: 1px solid var(--line); }
    .card-title { font-weight: 800; font-size: 1.1rem; }
    
    .pay-item { display: grid; grid-template-columns: 80px 1.5fr 1fr 120px; gap: 20px; padding: 20px; align-items: center; border-bottom: 1px solid var(--line); text-decoration: none; color: inherit; transition: background .2s; }
    .pay-item:last-child { border-bottom: none; }
    .pay-item:hover { background: var(--hover-bg); }
    
    .pay-img { width: 80px; height: 60px; border-radius: 10px; object-fit: cover; background: var(--dark2); }
    .pay-info { overflow: hidden; }
    .pay-name { font-weight: 700; margin-bottom: 4px; }
    .pay-meta { font-size: .85rem; color: var(--muted); }
    
    .pay-amount { font-weight: 800; color: var(--orange-l); font-size: 1.1rem; text-align: right; }
    
    .status-pill { font-size: .75rem; font-weight: 800; padding: 5px 12px; border-radius: 20px; text-transform: uppercase; text-align: center; }
    .s-pending { background: rgba(245,158,11,.15); color: #fbbf24; }
    .s-awaiting { background: rgba(59,130,246,.15); color: #60a5fa; }
    
    .empty { text-align: center; padding: 60px 20px; color: var(--muted); }
</style>
@endpush

@section('content')
<div class="wrap">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Outstanding Payments</span>
        </div>
        <div class="card-body" style="padding: 0">
            @forelse($bookings as $b)
            <a href="{{ $b->status === 'pending_payment' ? route('customer.payment.show', $b) : route('customer.tracking.show', $b) }}" class="pay-item">
                <img src="{{ $b->vehicle->image_url }}" class="pay-img">
                <div class="pay-info">
                    <div class="pay-name">{{ $b->vehicle->name }}</div>
                    <div class="pay-meta">Booking #{{ $b->id }} · {{ $b->pickup_date->format('M d') }} - {{ $b->return_date->format('M d') }}</div>
                </div>
                <div class="pay-amount">₱{{ number_format($b->total_amount, 0) }}</div>
                <div>
                    @if($b->status === 'pending_payment')
                        <div class="status-pill s-pending">Pay Now</div>
                    @else
                        <div class="status-pill s-awaiting">Verifying</div>
                    @endif
                </div>
            </a>
            @empty
            <div class="empty">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 16px; opacity: .3"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                <p>No payments required at the moment.</p>
                <p style="font-size: .85rem; margin-top: 8px">Payments will appear here once an admin approves your booking request.</p>
            </div>
            @endforelse
        </div>
    </div>

    <div style="background: rgba(255,107,0,.05); border: 1px solid var(--og); border-radius: 16px; padding: 20px; display: flex; gap: 16px; align-items: flex-start">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--orange-l)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div style="font-size: .88rem; line-height: 1.5">
            <strong style="display: block; margin-bottom: 4px; color: var(--text)">Payment Policy</strong>
            Approved bookings must be paid within <strong>1 hour</strong> to secure the vehicle. Failure to pay within the time limit will result in automatic cancellation of the reservation.
        </div>
    </div>
</div>
@endsection
