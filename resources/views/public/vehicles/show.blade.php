@extends('layouts.customer')
@section('title', $vehicle->name . ' — OrangeCrush')
@section('content')
@push('styles')
<style>
    .v-detail{display:grid;grid-template-columns:1.2fr 1fr;gap:32px;padding:48px 0}
    .v-img-wrap{border-radius:20px;overflow:hidden;aspect-ratio:16/10;background:var(--dark2)}
    .v-img-wrap img{width:100%;height:100%;object-fit:cover}
    .v-info{display:flex;flex-direction:column;gap:20px}
    .v-badge{display:inline-flex;padding:5px 12px;border-radius:999px;font-size:.8rem;font-weight:700;background:rgba(255,107,0,.15);color:#ff8c3a;width:fit-content}
    .v-title{font-size:2rem;font-weight:900;letter-spacing:-.04em;line-height:1.1}
    .v-brand-row{color:var(--muted);font-size:.95rem}
    .specs-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .spec-item{background:var(--card-bg);border:1px solid var(--line);border-radius:12px;padding:14px;display:flex;align-items:center;gap:10px}
    .spec-label{font-size:.75rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px}
    .spec-val{font-size:.95rem;font-weight:700}
    .price-box{background:rgba(255,107,0,.08);border:1px solid rgba(255,107,0,.2);border-radius:14px;padding:20px}
    .price-label{font-size:.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px}
    .price-val{font-size:2.2rem;font-weight:900;letter-spacing:-.04em;color:#ff8c3a}
    .price-per{font-size:.88rem;color:var(--muted)}
    .book-btn{display:block;width:100%;height:52px;background:linear-gradient(135deg,#ff8c3a,#ff6b00);border:none;border-radius:14px;color:#fff;font-family:inherit;font-size:1rem;font-weight:700;cursor:pointer;text-decoration:none;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 20px rgba(255,107,0,.3);transition:filter .2s,transform .2s}
    .book-btn:hover{filter:brightness(1.1);transform:translateY(-2px)}
    .desc{color:var(--muted);line-height:1.7;font-size:.95rem}
    @media(max-width:800px){.v-detail{grid-template-columns:1fr}}
</style>
@endpush
<div class="container">
    <div style="padding:20px 0 4px"><a href="{{ route('vehicles.index') }}" style="color:var(--muted);text-decoration:none;font-size:.9rem">← Back to vehicles</a></div>
    <div class="v-detail">
        <div>
            <div class="v-img-wrap"><img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->name }}"></div>
            @if($vehicle->description)<p class="desc" style="margin-top:24px">{{ $vehicle->description }}</p>@endif
        </div>
        <div class="v-info">
            <span class="v-badge">{{ $vehicle->type }}</span>
            <div>
                <h1 class="v-title">{{ $vehicle->name }}</h1>
                <p class="v-brand-row">{{ $vehicle->brand ?? $vehicle->category?->category_name }} · {{ $vehicle->year }}</p>
            </div>
            <div class="specs-grid">
                <div class="spec-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ff8c3a" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    <div><div class="spec-label">Capacity</div><div class="spec-val">{{ $vehicle->capacity }} seats</div></div>
                </div>
                <div class="spec-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ff8c3a" stroke-width="2"><path d="M13 2L3 14h7l-1 8 10-12h-7z"/></svg>
                    <div><div class="spec-label">Transmission</div><div class="spec-val">{{ $vehicle->transmission }}</div></div>
                </div>
                <div class="spec-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ff8c3a" stroke-width="2"><path d="M14 3h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h2"/></svg>
                    <div><div class="spec-label">Fuel</div><div class="spec-val">{{ $vehicle->fuel }}</div></div>
                </div>
                <div class="spec-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ff8c3a" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
                    <div><div class="spec-label">Plate</div><div class="spec-val">{{ $vehicle->plate_number ?? 'N/A' }}</div></div>
                </div>
            </div>
            <div class="price-box">
                <div class="price-label">Rental Rate</div>
                <div class="price-val">PHP {{ number_format($vehicle->price_per_day,0) }}</div>
                <div class="price-per">per day</div>
            </div>
            @if($vehicle->isAvailable())
                @auth
                    <a href="{{ route('customer.booking.create', ['vehicle' => $vehicle->id]) }}" class="book-btn">Book This Vehicle</a>
                @else
                    <a href="{{ route('login') }}?redirect={{ urlencode(route('customer.booking.create', ['vehicle' => $vehicle->id])) }}" class="book-btn">Sign In to Book</a>
                @endauth
            @else
                <div style="text-align:center;padding:16px;background:rgba(239,68,68,.1);border-radius:14px;color:#f87171;font-weight:600">This vehicle is currently not available</div>
            @endif
        </div>
    </div>
</div>
@endsection
