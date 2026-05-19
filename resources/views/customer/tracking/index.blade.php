@extends('layouts.customer')
@section('title','My Bookings')
@section('title_display', 'My Bookings')

@push('styles')
<style>
    .track-wrap{max-width:900px;margin:40px auto;padding:0 24px 80px}
    
    .tabs-nav { display: flex; gap: 8px; border-bottom: 1px solid var(--line); margin-bottom: 24px; padding-bottom: 2px; overflow-x: auto; -ms-overflow-style: none; scrollbar-width: none; }
    .tabs-nav::-webkit-scrollbar { display: none; }
    .tab-link { padding: 10px 20px; border-radius: 12px 12px 0 0; font-size: .9rem; font-weight: 600; color: var(--muted); text-decoration: none; border-bottom: 2px solid transparent; transition: all .2s; white-space: nowrap; }
    .tab-link:hover { color: var(--text); background: var(--hover-bg); }
    .tab-link.active { color: var(--orange-l); border-bottom-color: var(--orange-l); background: rgba(255,107,0,.05); }

    .booking-card{background:var(--card-bg);border:1px solid var(--line);border-radius:18px;padding:20px 24px;margin-bottom:16px;display:flex;align-items:center;gap:20px;text-decoration:none;color:inherit;transition:all .2s}
    .booking-card:hover{background:var(--hover-bg);border-color:rgba(255,107,0,.25);transform:translateY(-2px)}
    .bc-img{width:100px;height:75px;border-radius:12px;object-fit:cover;flex-shrink:0;background:var(--dark2)}
    .bc-info{flex:1}
    .bc-name{font-size:1.05rem;font-weight:800;margin-bottom:6px;letter-spacing:-.01em}
    .bc-dates{font-size:.85rem;color:var(--text-dim);display:flex;align-items:center;gap:6px}
    .bc-right{display:flex;flex-direction:column;align-items:flex-end;gap:10px}
    .bc-price{font-size:1.1rem;font-weight:900;color:var(--text)}
    
    .badge{display:inline-flex;padding:5px 12px;border-radius:20px;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
    .s-awaiting_approval{background:rgba(59,130,246,.12);color:#60a5fa}
    .s-pending_payment{background:rgba(245,158,11,.12);color:#fbbf24}
    .s-awaiting_verification{background:rgba(59,130,246,.12);color:#60a5fa}
    .s-partial_paid{background:rgba(255,107,0,.12);color:#ff8c3a}
    .s-fully_paid{background:rgba(34,197,94,.12);color:#4ade80}
    .s-confirmed{background:rgba(34,197,94,.12);color:#4ade80}
    .s-rejected{background:rgba(239,68,68,.12);color:#f87171}
    .s-ongoing{background:rgba(255,107,0,.12);color:#ff8c3a}
    .s-completed{background:var(--badge-y);color:var(--muted)}
    .s-cancelled{background:rgba(239,68,68,.12);color:#f87171}
    .s-no_show{background:rgba(239,68,68,.12);color:#f87171}
    
    .empty{text-align:center;padding:100px 20px;color:var(--text-dim)}
    .empty svg{margin-bottom:20px;opacity:.2}
    .empty a{color:var(--orange-l);text-decoration:none;font-weight:700}
</style>
@endpush

@section('content')
<div class="track-wrap">
    <div class="tabs-nav">
        <a href="?tab=upcoming" class="tab-link {{ $tab === 'upcoming' ? 'active' : '' }}">Upcoming</a>
        <a href="?tab=ongoing" class="tab-link {{ $tab === 'ongoing' ? 'active' : '' }}">Ongoing</a>
        <a href="?tab=past" class="tab-link {{ $tab === 'past' ? 'active' : '' }}">Past Rentals</a>
        <a href="?tab=cancelled" class="tab-link {{ $tab === 'cancelled' ? 'active' : '' }}">Cancelled</a>
        <a href="?tab=no_show" class="tab-link {{ $tab === 'no_show' ? 'active' : '' }}">No Show</a>
    </div>

    @forelse($bookings as $booking)
    <a href="{{ route('customer.tracking.show', $booking) }}" class="booking-card">
        <img class="bc-img" src="{{ $booking->vehicle->image_url }}" alt="{{ $booking->vehicle->name }}">
        <div class="bc-info">
            <div class="bc-name">{{ $booking->vehicle->name }}</div>
            <div class="bc-dates">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                {{ $booking->pickup_date->format('M d') }} → {{ $booking->return_date->format('M d, Y') }}
                · {{ $booking->duration_in_days }} day(s)
            </div>
        </div>
        <div class="bc-right">
            <span class="badge s-{{ $booking->status }}">{{ str_replace('_',' ',$booking->status) }}</span>
            <span class="bc-price">₱{{ number_format($booking->total_amount,0) }}</span>
        </div>
    </a>
    @empty
    <div class="empty">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <p style="font-size:1.15rem;margin-bottom:12px;font-weight:600">No {{ $tab }} bookings found.</p>
        <p><a href="{{ route('vehicles.index') }}">Browse available vehicles →</a></p>
    </div>
    @endforelse
</div>
@endsection
