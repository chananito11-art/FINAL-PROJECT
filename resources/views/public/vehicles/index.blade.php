@extends('layouts.customer')
@section('title','Browse Vehicles')
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
<style>
    .hero{padding:60px 0 40px;text-align:center}
    .hero h1{font-size:clamp(2rem,5vw,3rem);font-weight:900;letter-spacing:-.05em;margin-bottom:12px}
    .hero h1 span{background:linear-gradient(90deg,#ff8c3a,#ff6b00);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
    .hero p{color:var(--muted);font-size:1.05rem}
    
    .search-box {
        background: var(--card-bg);
        border: 1px solid var(--line);
        border-radius: 24px;
        padding: 32px;
        margin-bottom: 48px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        backdrop-filter: blur(10px);
        transition: box-shadow .3s, border-color .3s;
    }
    .search-box.needs-dates {
        border-color: rgba(255,107,0,.45);
        box-shadow: 0 0 0 4px rgba(255,107,0,.1), 0 20px 40px rgba(0,0,0,0.1);
        animation: pulse-border 2s ease-in-out infinite;
    }
    @keyframes pulse-border {
        0%,100%{box-shadow:0 0 0 4px rgba(255,107,0,.1),0 20px 40px rgba(0,0,0,.1)}
        50%{box-shadow:0 0 0 8px rgba(255,107,0,.15),0 20px 40px rgba(0,0,0,.1)}
    }
    .date-prompt-banner {
        background: linear-gradient(135deg,rgba(255,107,0,.12),rgba(255,107,0,.06));
        border: 1px solid rgba(255,107,0,.3);
        border-radius: 14px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 20px;
    }
    .date-prompt-banner .icon{font-size:1.6rem;flex-shrink:0}
    .date-prompt-banner strong{display:block;font-size:.95rem;color:var(--orange-l)}
    .date-prompt-banner span{font-size:.83rem;color:var(--muted)}
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        align-items: flex-end;
    }
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .filter-group label {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--muted);
    }
    .filter-input {
        background: var(--input-bg);
        border: 1px solid var(--line);
        border-radius: 12px;
        height: 48px;
        padding: 0 16px;
        color: var(--text);
        font-family: inherit;
        font-size: 0.95rem;
        outline: none;
        transition: all 0.2s;
        width: 100%;
    }
    .filter-input:focus {
        border-color: var(--orange);
        box-shadow: 0 0 0 4px rgba(255,107,0,0.1);
    }
    .apply-btn {
        background: linear-gradient(135deg, var(--orange-l), var(--orange));
        color: white;
        border: none;
        border-radius: 12px;
        height: 48px;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .apply-btn:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
    }

    .grid{display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:32px;padding-bottom:60px}
    .v-card{background:var(--card-bg);border:1px solid var(--line);border-radius:24px;overflow:hidden;transition:transform .2s,box-shadow .2s}
    .v-card:hover{transform:translateY(-6px);box-shadow:0 24px 48px rgba(0,0,0,.18)}
    .v-img{aspect-ratio:16/10;overflow:hidden;position:relative;background:var(--dark2)}
    .v-img img{width:100%;height:100%;object-fit:cover;display:block}
    .v-type{position:absolute;top:14px;right:14px;background:var(--dark);color:var(--text);padding:7px 12px;border-radius:999px;font-size:.82rem;font-weight:700;backdrop-filter:blur(8px);border:1px solid var(--line)}
    .v-body{padding:24px}
    .v-name{font-size:1.1rem;font-weight:800;letter-spacing:-.02em;margin-bottom:4px}
    .v-brand{font-size:.88rem;color:var(--muted);margin-bottom:14px}
    .v-specs{display:flex;flex-wrap:wrap;gap:14px;color:var(--muted);font-size:.85rem;margin-bottom:16px}
    .v-spec{display:inline-flex;align-items:center;gap:5px}
    .v-footer{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;margin-top:16px;padding-top:16px;border-top:1px solid var(--line)}
    .v-price{font-size:1.2rem;font-weight:900;letter-spacing:-.03em}
    .v-price small{display:block;font-size:.8rem;font-weight:500;color:var(--muted);margin-top:1px}
    .book-btn{background:var(--dark2);color:var(--text);border:1px solid var(--line);border-radius:12px;padding:11px 18px;font-size:.88rem;font-weight:700;text-decoration:none;cursor:pointer;font-family:inherit;transition:all .2s}
    .book-btn:hover{background:var(--hover-bg)}
    .empty{text-align:center;padding:80px 20px;color:var(--text-dim);grid-column:1/-1}
</style>
@endpush
@section('content')
<div class="container">
    <div class="hero">
        <h1>Browse our <span>Premium Fleet</span></h1>
        @if(request()->filled(['pickup_date','return_date']))
        @php
            $pickup = \Carbon\Carbon::parse(request('pickup_date'));
            $ret    = \Carbon\Carbon::parse(request('return_date'));
            $days   = max(1, $pickup->diffInDays($ret));
        @endphp
        <p style="color:#ff8c3a;font-weight:700">
            ✓ Showing cars available {{ $pickup->format('M d') }} → {{ $ret->format('M d, Y') }} · {{ $days }} day{{ $days !== 1 ? 's' : '' }}
        </p>
        @else
        <p>Choose your dates below to see only available vehicles</p>
        @endif
    </div>

    <form action="{{ route('vehicles.index') }}" method="GET" class="search-box{{ !request()->filled(['pickup_date','return_date']) ? ' needs-dates' : '' }}" id="vehicleFilterForm">
        @if(!request()->filled(['pickup_date','return_date']))
        <div class="date-prompt-banner">
            <div class="icon">📅</div>
            <div>
                <strong>Select your rental dates first</strong>
                <span>Pick a pickup and return date to see only available vehicles for your trip.</span>
            </div>
        </div>
        @endif
        <div class="filter-grid">
            <div class="filter-group">
                <label>Pickup Date</label>
                <input type="text" name="pickup_date" id="pickup_date" class="filter-input datepicker" placeholder="Select Date" value="{{ request('pickup_date') }}" autocomplete="off" required>
            </div>
            <div class="filter-group">
                <label>Return Date</label>
                <input type="text" name="return_date" id="return_date" class="filter-input datepicker" placeholder="Select Date" value="{{ request('return_date') }}" autocomplete="off" required>
            </div>
            <div class="filter-group">
                <label>Vehicle Type</label>
                <select name="type" class="filter-input">
                    <option value="">All Types</option>
                    @foreach(['Sedan','SUV','Pickup Truck','Van','Hatchback','Crossover'] as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Min Capacity</label>
                <select name="capacity" class="filter-input">
                    <option value="">Any Capacity</option>
                    @for($i=2; $i<=10; $i++)
                        <option value="{{ $i }}" {{ request('capacity') == $i ? 'selected' : '' }}>{{ $i }}+ Seats</option>
                    @endfor
                </select>
            </div>
            <div class="filter-group">
                <button type="submit" class="apply-btn">Show Available Cars</button>
            </div>
        </div>
    </form>

    <div class="grid" id="vehicleGrid">
        @forelse($vehicles as $vehicle)
        <article class="v-card">
            <div class="v-img">
                <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->name }}" loading="lazy">
                <span class="v-type">{{ $vehicle->type }}</span>
            </div>
            <div class="v-body">
                <div class="v-name">{{ $vehicle->name }}</div>
                <div class="v-brand">{{ $vehicle->brand ?? 'Premium Fleet' }}</div>
                <div class="v-specs">
                    <span class="v-spec">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
                        {{ $vehicle->capacity }} seats
                    </span>
                    <span class="v-spec">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h7l-1 8 10-12h-7z"/></svg>
                        {{ $vehicle->transmission }}
                    </span>
                    <span class="v-spec">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 3h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h2"/></svg>
                        {{ $vehicle->fuel }}
                    </span>
                </div>
                <div class="v-footer">
                    <div class="v-price">
                        <small style="margin-bottom:2px; font-weight:700; color:var(--orange-l);">Starts at</small>
                        PHP {{ number_format($vehicle->price_per_day,0) }}<small>per day</small>
                    </div>
                    @php
                        $bookingUrl = route('customer.booking.create', [
                            'vehicle' => $vehicle->id,
                            'pickup' => request('pickup_date'),
                            'return' => request('return_date')
                        ]);
                    @endphp
                    @auth
                        <a href="{{ $bookingUrl }}" class="book-btn">Book Now</a>
                    @else
                        <a href="{{ route('login') }}?redirect={{ urlencode($bookingUrl) }}" class="book-btn">Book Now</a>
                    @endauth
                </div>
            </div>
        </article>
        @empty
        <div class="empty">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--line)" stroke-width="1.5" style="margin-bottom: 20px"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            @if(request()->filled(['pickup_date','return_date']))
            <p>No vehicles available for <strong>{{ request('pickup_date') }}</strong> &rarr; <strong>{{ request('return_date') }}</strong>.</p>
            <p style="font-size:.88rem; color:var(--muted)">Try different dates or a different vehicle type.</p>
            <a href="{{ route('vehicles.index') }}" style="color: var(--orange); text-decoration: none; font-weight: 700; margin-top: 12px; display: inline-block">← Change Dates</a>
            @else
            <p>No vehicles found. <a href="{{ route('vehicles.index') }}" style="color:var(--orange);text-decoration:none;font-weight:700">Clear filters</a></p>
            @endif
        </div>
        @endforelse
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    const hasDates = {{ request()->filled(['pickup_date','return_date']) ? 'true' : 'false' }};

    const fpPickup = flatpickr("#pickup_date", {
        minDate: "today",
        dateFormat: "Y-m-d",
        defaultDate: "{{ request('pickup_date') }}",
        onChange: function(selectedDates, dateStr) {
            fpReturn.set('minDate', dateStr);
        }
    });

    const fpReturn = flatpickr("#return_date", {
        minDate: "today",
        dateFormat: "Y-m-d",
        defaultDate: "{{ request('return_date') }}",
    });

    // Auto-open pickup date when arriving without dates
    if (!hasDates) {
        setTimeout(() => fpPickup.open(), 600);
    }
</script>
@endpush

