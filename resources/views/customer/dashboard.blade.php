@extends('layouts.customer')

@section('title', 'My Dashboard - OrangeCrush')

@push('styles')
<style>
    .dash-wrap { max-width: 1100px; margin: 40px auto; padding: 0 20px 80px; }
    .dash-header { margin-bottom: 32px; }
    .dash-header h1 { font-size: 2rem; font-weight: 900; letter-spacing: -.04em; margin-bottom: 8px; }
    .dash-header p { color: var(--muted); font-size: 1.1rem; }
    
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 40px; }
    .stat-card { background: var(--card-bg); border: 1px solid var(--line); border-radius: 20px; padding: 24px; position: relative; overflow: hidden; transition: transform .2s; }
    .stat-card:hover { transform: translateY(-4px); border-color: rgba(255,107,0,0.3); }
    .stat-label { font-size: .85rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 12px; }
    .stat-value { font-size: 2.2rem; font-weight: 900; letter-spacing: -.04em; margin-bottom: 4px; }
    .stat-sub { font-size: .88rem; color: var(--muted); }
    
    .section-title { font-size: 1.25rem; font-weight: 800; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; }
    .view-all { font-size: .9rem; font-weight: 600; color: var(--orange-l); text-decoration: none; }
    
    .main-grid { display: grid; grid-template-columns: 1.6fr 1fr; gap: 32px; }
    
    .card { background: var(--card-bg); border: 1px solid var(--line); border-radius: 20px; overflow: hidden; }
    .card-header { padding: 20px 24px; border-bottom: 1px solid var(--line); font-weight: 700; }
    .card-body { padding: 24px; }
    
    .booking-item { display: flex; align-items: center; gap: 16px; padding: 16px; border-radius: 14px; background: var(--ghost-bg); margin-bottom: 12px; text-decoration: none; color: inherit; transition: background .2s; }
    .booking-item:hover { background: var(--ghost-hover); }
    .booking-img { width: 64px; height: 48px; border-radius: 8px; object-fit: cover; background: var(--dark); }
    .booking-info { flex: 1; }
    .booking-name { font-weight: 700; font-size: .95rem; margin-bottom: 2px; }
    .booking-meta { font-size: .82rem; color: var(--muted); }
    .booking-status { font-size: .75rem; font-weight: 800; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; }
    
    .vehicle-card { display: flex; flex-direction: column; gap: 12px; padding: 16px; border-radius: 16px; background: var(--ghost-bg); margin-bottom: 16px; text-decoration: none; color: inherit; transition: transform .2s; }
    .vehicle-card:hover { transform: scale(1.02); }
    .vehicle-img { width: 100%; aspect-ratio: 16/9; border-radius: 12px; object-fit: cover; background: var(--dark); }
    .vehicle-name { font-weight: 700; font-size: 1rem; }
    .vehicle-price { font-weight: 800; color: var(--orange-l); font-size: 1.1rem; }

    @media (max-width: 850px) {
        .main-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="dash-wrap">
    <div class="dash-header">
        <h1>Welcome back, {{ auth()->user()->first_name }}!</h1>
        <p>Manage your car rentals and view your booking status below.</p>
    </div>

    {{-- Verification Alert --}}
    @if(auth()->user()->verification_status !== 'verified')
    <div style="background:{{ auth()->user()->verification_status === 'rejected' ? 'rgba(239,68,68,.1)' : 'rgba(255,107,0,.1)' }}; border: 1px solid {{ auth()->user()->verification_status === 'rejected' ? 'rgba(239,68,68,.2)' : 'rgba(255,107,0,.2)' }}; border-radius: 20px; padding: 24px; margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; gap: 24px">
        <div style="display:flex; gap:20px; align-items:center">
            <div style="width:50px; height:50px; border-radius:50%; background:{{ auth()->user()->verification_status === 'rejected' ? '#ef4444' : 'var(--orange)' }}; color:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0">
                @if(auth()->user()->verification_status === 'rejected')
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                @else
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                @endif
            </div>
            <div>
                <h3 style="font-size:1.1rem; margin-bottom:4px">
                    @if(auth()->user()->verification_status === 'unverified')
                        Identity Verification Required
                    @elseif(auth()->user()->verification_status === 'pending')
                        Verification in Progress
                    @elseif(auth()->user()->verification_status === 'rejected')
                        Verification Rejected
                    @elseif(auth()->user()->verification_status === 'expired')
                        Document Expired
                    @endif
                </h3>
                <p style="font-size:.9rem; color:var(--muted)">
                    @if(auth()->user()->verification_status === 'unverified')
                        Please upload your Driver's License to enable instant booking approvals.
                    @elseif(auth()->user()->verification_status === 'pending')
                        Our team is currently reviewing your documents. This usually takes 1-2 hours.
                    @elseif(auth()->user()->verification_status === 'rejected')
                        Your submitted documents were rejected. Please check the notes and re-upload.
                    @elseif(auth()->user()->verification_status === 'expired')
                        Your ID on file has expired. Please provide a valid document.
                    @endif
                </p>
            </div>
        </div>
        @if(auth()->user()->verification_status !== 'pending')
        <a href="{{ route('customer.verification.show') }}" class="btn btn-primary" style="flex-shrink:0">Verify Now</a>
        @endif
    </div>
    @endif

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Bookings</div>
            <div class="stat-value">{{ $stats['total_bookings'] }}</div>
            <div class="stat-sub">Lifetime reservations</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active Rentals</div>
            <div class="stat-value" style="color: var(--green)">{{ $stats['active_rentals'] }}</div>
            <div class="stat-sub">Confirmed & Ongoing</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending Approval</div>
            <div class="stat-value" style="color: var(--orange-l)">{{ $stats['pending_approval'] }}</div>
            <div class="stat-sub">Awaiting admin review</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Spent</div>
            <div class="stat-value">₱{{ number_format($stats['total_spent'], 0) }}</div>
            <div class="stat-sub">Completed payments</div>
        </div>
    </div>

    <div class="main-grid">
        <div>
            <div class="section-title">
                <span>Recent Bookings</span>
                <a href="{{ route('customer.tracking.index') }}" class="view-all">View All →</a>
            </div>
            
            <div class="card">
                <div class="card-body">
                    @forelse($recentBookings as $booking)
                    <a href="{{ route('customer.tracking.show', $booking) }}" class="booking-item">
                        <img src="{{ $booking->vehicle->image_url }}" class="booking-img">
                        <div class="booking-info">
                            <div class="booking-name">{{ $booking->vehicle->name }}</div>
                            <div class="booking-meta">
                                {{ $booking->pickup_date->format('M d') }} - {{ $booking->return_date->format('M d, Y') }}
                            </div>
                        </div>
                        @php
                            $colors = [
                                'awaiting_approval' => ['bg'=>'rgba(59,130,246,.15)', 'c'=>'#60a5fa'],
                                'pending_payment'   => ['bg'=>'rgba(245,158,11,.15)', 'c'=>'#fbbf24'],
                                'awaiting_verification' => ['bg'=>'rgba(59,130,246,.15)', 'c'=>'#60a5fa'],
                                'partial_paid'      => ['bg'=>'rgba(255,107,0,.15)', 'c'=>'#ff8c3a'],
                                'fully_paid'        => ['bg'=>'rgba(34,197,94,.15)', 'c'=>'#4ade80'],
                                'confirmed'         => ['bg'=>'rgba(34,197,94,.15)', 'c'=>'#4ade80'],
                                'ongoing'           => ['bg'=>'rgba(255,107,0,.15)', 'c'=>'#ff8c3a'],
                                'completed'         => ['bg'=>'rgba(255,255,255,.08)', 'c'=>'var(--muted)'],
                                'cancelled'         => ['bg'=>'rgba(239,68,68,.15)', 'c'=>'#f87171'],
                                'rejected'          => ['bg'=>'rgba(239,68,68,.15)', 'c'=>'#f87171'],
                            ];
                            $c = $colors[$booking->status] ?? ['bg'=>'#eee', 'c'=>'#333'];
                        @endphp
                        <span class="booking-status" style="background: {{ $c['bg'] }}; color: {{ $c['c'] }}">
                            {{ str_replace('_', ' ', $booking->status) }}
                        </span>
                    </a>
                    @empty
                    <div style="text-align: center; padding: 40px; color: var(--muted)">
                        <p style="margin-bottom: 20px">You haven't made any bookings yet.</p>
                        <a href="{{ route('vehicles.index') }}" class="btn btn-primary">Browse Vehicles</a>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div>
            <div class="section-title">
                <span>Quick Book</span>
            </div>
            
            @foreach($recommendedVehicles as $vehicle)
            <a href="{{ route('vehicles.show', $vehicle->id) }}" class="vehicle-card">
                <img src="{{ $vehicle->image_url }}" class="vehicle-img">
                <div style="display: flex; justify-content: space-between; align-items: flex-end">
                    <div>
                        <div class="vehicle-name">{{ $vehicle->name }}</div>
                        <div style="font-size: .82rem; color: var(--muted)">{{ $vehicle->type }} · {{ $vehicle->transmission }}</div>
                    </div>
                    <div class="vehicle-price">₱{{ number_format($vehicle->price_per_day, 0) }}<span style="font-size: .75rem; color: var(--muted); font-weight: 500">/day</span></div>
                </div>
            </a>
            @endforeach
            
            <a href="{{ route('vehicles.index') }}" class="btn btn-ghost" style="width: 100%; justify-content: center; margin-top: 8px">View More Vehicles →</a>
        </div>
    </div>
</div>
@endsection
