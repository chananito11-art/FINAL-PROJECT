@extends('layouts.customer')
@section('title','Browse Vehicles')
@push('styles')
<style>
    .hero{padding:60px 0 40px;text-align:center}
    .hero h1{font-size:clamp(2rem,5vw,3rem);font-weight:900;letter-spacing:-.05em;margin-bottom:12px}
    .hero h1 span{background:linear-gradient(90deg,#ff8c3a,#ff6b00);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
    .hero p{color:var(--muted);font-size:1.05rem}
    .filters{background:var(--card-bg);border:1px solid var(--line);border-radius:16px;padding:20px 24px;margin:0 0 32px;display:flex;gap:14px;flex-wrap:wrap;align-items:center}
    .filter-field{position:relative;flex:1;min-width:180px}
    .filter-icon{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--text-dim);pointer-events:none}
    .filter-control{width:100%;height:42px;background:var(--input-bg);border:1px solid var(--line);border-radius:10px;color:var(--text);font-family:inherit;font-size:.9rem;padding:0 12px 0 38px;outline:none;transition:border-color .2s}
    .filter-control:focus{border-color:var(--orange)}
    select.filter-control{-webkit-appearance:none;cursor:pointer}
    .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
    .v-card{background:var(--card-bg);border:1px solid var(--line);border-radius:20px;overflow:hidden;transition:transform .2s,box-shadow .2s}
    .v-card:hover{transform:translateY(-4px);box-shadow:0 20px 40px rgba(0,0,0,.15)}
    .v-img{aspect-ratio:16/10;overflow:hidden;position:relative;background:var(--dark2)}
    .v-img img{width:100%;height:100%;object-fit:cover;display:block}
    .v-type{position:absolute;top:12px;right:12px;background:var(--dark);color:var(--text);padding:6px 11px;border-radius:999px;font-size:.82rem;font-weight:700}
    .v-status{position:absolute;top:12px;left:12px;padding:5px 10px;border-radius:999px;font-size:.75rem;font-weight:700}
    .v-body{padding:20px}
    .v-name{font-size:1rem;font-weight:800;letter-spacing:-.02em;margin-bottom:4px}
    .v-brand{font-size:.88rem;color:var(--muted);margin-bottom:14px}
    .v-specs{display:flex;flex-wrap:wrap;gap:14px;color:var(--muted);font-size:.85rem;margin-bottom:16px}
    .v-spec{display:inline-flex;align-items:center;gap:5px}
    .v-footer{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;margin-top:16px}
    .v-price{font-size:1.2rem;font-weight:900;letter-spacing:-.03em}
    .v-price small{display:block;font-size:.8rem;font-weight:500;color:var(--muted);margin-top:1px}
    .book-btn{background:var(--dark2);color:var(--text);border:1px solid var(--line);border-radius:12px;padding:11px 18px;font-size:.88rem;font-weight:700;text-decoration:none;cursor:pointer;font-family:inherit;transition:all .2s}
    .book-btn:hover{background:var(--hover-bg)}
    .empty{text-align:center;padding:80px 20px;color:var(--text-dim)}
    .hidden{display:none!important}
    @media(max-width:1000px){.grid{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:640px){.grid{grid-template-columns:1fr}.filters{flex-direction:column}}
</style>
@endpush
@section('content')
<div class="container">
    <div class="hero">
        <h1>Browse our <span>Premium Fleet</span></h1>
        <p>Find the perfect vehicle for your next adventure</p>
    </div>
    <div class="filters">
        <div class="filter-field">
            <svg class="filter-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
            <input class="filter-control" id="search" type="text" placeholder="Search vehicles…">
        </div>
        <div class="filter-field">
            <svg class="filter-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            <select class="filter-control" id="typeFilter">
                <option value="">All Types</option>
                @foreach($vehicles->pluck('type')->unique() as $t)<option value="{{ strtolower($t) }}">{{ $t }}</option>@endforeach
            </select>
        </div>
        <div class="filter-field">
            <svg class="filter-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <select class="filter-control" id="brandFilter">
                <option value="">All Brands</option>
                @foreach($categories as $cat)<option value="{{ strtolower($cat->category_name) }}">{{ $cat->category_name }}</option>@endforeach
            </select>
        </div>
        <span id="countBadge" style="font-size:.88rem;color:var(--text-dim);white-space:nowrap">{{ $vehicles->count() }} vehicles</span>
    </div>
    <div class="grid" id="vehicleGrid">
        @forelse($vehicles as $vehicle)
        <article class="v-card" data-name="{{ strtolower($vehicle->name) }}" data-brand="{{ strtolower($vehicle->brand ?? '') }}" data-type="{{ strtolower($vehicle->type) }}">
            <div class="v-img">
                <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->name }}" loading="lazy">
                <span class="v-type">{{ $vehicle->type }}</span>
            </div>
            <div class="v-body">
                <div class="v-name">{{ $vehicle->name }}</div>
                <div class="v-brand">{{ $vehicle->brand ?? $vehicle->category?->category_name ?? 'Premium Fleet' }}</div>
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
                    <div class="v-price">PHP {{ number_format($vehicle->price_per_day,0) }}<small>per day</small></div>
                    @auth
                        <a href="{{ route('customer.booking.create', ['vehicle' => $vehicle->id]) }}" class="book-btn">Book Now</a>
                    @else
                        <a href="{{ route('login') }}?redirect={{ urlencode(route('customer.booking.create', ['vehicle' => $vehicle->id])) }}" class="book-btn">Book Now</a>
                    @endauth
                </div>
            </div>
        </article>
        @empty
        <div class="empty" style="grid-column:1/-1"><p>No vehicles available at the moment.</p></div>
        @endforelse
    </div>
</div>
@endsection
@push('scripts')
<script>
const s=document.getElementById('search'),tf=document.getElementById('typeFilter'),bf=document.getElementById('brandFilter');
const cards=[...document.querySelectorAll('.v-card')],cnt=document.getElementById('countBadge');
function filter(){const q=s.value.toLowerCase(),t=tf.value,b=bf.value;let n=0;
cards.forEach(c=>{const m=(!q||c.dataset.name.includes(q)||c.dataset.brand.includes(q))&&(!t||c.dataset.type===t)&&(!b||c.dataset.brand===b);c.classList.toggle('hidden',!m);if(m)n++;});
cnt.textContent=n+' vehicle'+(n!==1?'s':'');}
s.addEventListener('input',filter);tf.addEventListener('change',filter);bf.addEventListener('change',filter);
</script>
@endpush
