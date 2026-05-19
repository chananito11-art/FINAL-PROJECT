@extends('layouts.app')
@section('title','Ongoing Rentals')
@section('page-title','Ongoing Rentals')
@push('styles')
<style>
.rental-grid{display:grid;gap:16px}
.rental-card{background:var(--card-bg);border:1px solid var(--line);border-radius:16px;overflow:hidden;transition:border-color .2s}
.rental-card:hover{border-color:rgba(255,107,0,.3)}
.rental-card.overdue{border-color:rgba(239,68,68,.4);background:rgba(239,68,68,.03)}
.rc-header{padding:14px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--line)}
.rc-body{padding:16px 20px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
.rc-footer{padding:12px 20px;background:var(--ghost-bg);border-top:1px solid var(--line);display:flex;align-items:center;gap:10px}
.rc-stat{font-size:.8rem;color:var(--muted)}
.rc-stat strong{display:block;font-size:.95rem;color:var(--text);margin-top:2px}
.stat-summary{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px}
.ss-card{background:var(--card-bg);border:1px solid var(--line);border-radius:14px;padding:18px 20px}
.ss-label{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:6px}
.ss-value{font-size:1.8rem;font-weight:900;letter-spacing:-.04em}
.overdue-banner{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);border-radius:12px;padding:12px 18px;display:flex;align-items:center;gap:12px;margin-bottom:20px;color:#f87171;font-size:.9rem;font-weight:600}
@media(max-width:760px){.rc-body{grid-template-columns:1fr 1fr}.stat-summary{grid-template-columns:1fr 1fr}}
</style>
@endpush
@section('content')

{{-- Summary Stats --}}
<div class="stat-summary">
    <div class="ss-card">
        <div class="ss-label">Active Rentals</div>
        <div class="ss-value" style="color:var(--orange-l)">{{ $rentals->total() }}</div>
    </div>
    <div class="ss-card">
        <div class="ss-label">Overdue</div>
        <div class="ss-value" style="color:var(--red)">{{ $overdueCount }}</div>
    </div>
    <div class="ss-card">
        <div class="ss-label">On Time</div>
        <div class="ss-value" style="color:var(--green)">{{ $rentals->total() - $overdueCount }}</div>
    </div>
</div>

{{-- Overdue Banner --}}
@if($overdueCount > 0)
<div class="overdue-banner">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ $overdueCount }} rental{{ $overdueCount > 1 ? 's are' : ' is' }} overdue and should be returned immediately.
</div>
@endif

{{-- Rental Cards --}}
@forelse($rentals as $rental)
@php
    $booking  = $rental->booking;
    $vehicle  = $rental->vehicle ?? $booking?->vehicle;
    $customer = $booking?->user
                ? ($booking->user->first_name . ' ' . $booking->user->last_name)
                : ($booking?->first_name . ' ' . $booking?->last_name);
    $isOverdue = $rental->isOverdue();
    $daysOut   = $rental->pickup_date ? (int) $rental->pickup_date->diffInDays(now()) : 0;
    $daysLeft  = $isOverdue
                 ? '— Overdue by ' . (int) $rental->expected_return_date->diffInDays(now()) . 'd'
                 : $rental->expected_return_date->diffForHumans();
@endphp
<div class="rental-card {{ $isOverdue ? 'overdue' : '' }}">
    <div class="rc-header">
        <div style="display:flex;align-items:center;gap:12px">
            <div>
                <div style="font-weight:700;font-size:.97rem">{{ $customer }}</div>
                <div style="font-size:.78rem;color:var(--muted)">
                    Rental #{{ $rental->id }} · Booking #{{ $booking?->id }}
                </div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            @if($isOverdue)
                <span class="badge br">⚠ Overdue</span>
            @else
                <span class="badge bg_">Active</span>
            @endif
            <span class="badge bgy">{{ $vehicle?->name }}</span>
        </div>
    </div>

    <div class="rc-body">
        <div class="rc-stat">
            <span>Picked Up</span>
            <strong>{{ $rental->pickup_date?->format('M d, Y') }}</strong>
            <span style="font-size:.75rem;color:var(--muted)">{{ $rental->pickup_date?->format('h:i A') }}</span>
        </div>
        <div class="rc-stat">
            <span>Due Return</span>
            <strong style="color:{{ $isOverdue ? 'var(--red)' : 'var(--text)' }}">
                {{ $rental->expected_return_date?->format('M d, Y') }}
            </strong>
            <span style="font-size:.75rem;color:{{ $isOverdue ? 'var(--red)' : 'var(--muted)' }}">
                {{ $daysLeft }}
            </span>
        </div>
        <div class="rc-stat">
            <span>Days Out</span>
            <strong>{{ $daysOut }} day{{ $daysOut !== 1 ? 's' : '' }}</strong>
            <span style="font-size:.75rem;color:var(--muted)">
                Odometer: {{ number_format($rental->pickup_odometer ?? 0) }} km
            </span>
        </div>
        <div class="rc-stat">
            <span>Fuel at Pickup</span>
            <strong>{{ $rental->pickup_fuel ?? '—' }}%</strong>
        </div>
        <div class="rc-stat">
            <span>Vehicle</span>
            <strong>{{ $vehicle?->plate_number ?? '—' }}</strong>
            <span style="font-size:.75rem;color:var(--muted)">{{ $vehicle?->type }}</span>
        </div>
        <div class="rc-stat">
            <span>Rental Amount</span>
            <strong style="color:var(--orange-l)">₱{{ number_format($booking?->total_amount ?? 0, 0) }}</strong>
        </div>
    </div>

    <div class="rc-footer">
        <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-ghost btn-sm">View Booking</a>
        @if(!$booking?->inspections()->where('type','return')->exists())
            <a href="{{ route('admin.bookings.inspection.create', $booking) }}?type=return"
               class="btn btn-primary btn-sm">Record Return Inspection</a>
        @else
            <a href="{{ route('admin.returns.index') }}" class="btn btn-success btn-sm">Process Return</a>
        @endif
        @if($isOverdue)
            <span style="margin-left:auto;font-size:.8rem;color:var(--red);font-weight:600">
                🔴 Past due date — contact customer
            </span>
        @endif
    </div>
</div>
@empty
<div class="card">
    <div class="card-body" style="text-align:center;padding:60px;color:var(--text-dim)">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.3;margin-bottom:16px"><path d="M3 13l1.5-4.5A3 3 0 0 1 7.35 6h9.3a3 3 0 0 1 2.85 2.5L21 13"/><path d="M5 16h14"/><circle cx="7.5" cy="15.5" r="1.5"/><circle cx="16.5" cy="15.5" r="1.5"/></svg>
        <div style="font-size:1rem;font-weight:700;margin-bottom:8px">No Active Rentals</div>
        <div style="font-size:.88rem">All vehicles are currently available. Active rentals will appear here after a pickup inspection is completed.</div>
    </div>
</div>
@endforelse

@if($rentals->hasPages())
<div style="margin-top:20px">{{ $rentals->links() }}</div>
@endif

@endsection
