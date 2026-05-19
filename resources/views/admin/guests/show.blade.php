@extends('layouts.app')
@section('title', 'Guest Profile — ' . $guest->full_name)
@section('page-title', 'Guest Profile')

@push('styles')
<style>
    .guest-hero{background:linear-gradient(135deg,rgba(255,107,0,.1) 0%,rgba(96,165,250,.06) 100%);border:1px solid rgba(255,107,0,.18);border-radius:20px;padding:28px;margin-bottom:24px;display:flex;align-items:center;gap:20px}
    .guest-av{width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#60a5fa,#3b82f6);display:grid;place-items:center;font-size:1.6rem;font-weight:900;color:#fff;flex-shrink:0;box-shadow:0 0 22px rgba(96,165,250,.35)}
    .stat-chips{display:flex;gap:10px;flex-wrap:wrap;margin-top:10px}
    .chip{padding:4px 12px;border-radius:999px;font-size:.75rem;font-weight:700;border:1px solid var(--line);color:var(--muted)}
    .chip.orange{border-color:rgba(255,107,0,.3);background:rgba(255,107,0,.08);color:var(--orange-l)}
</style>
@endpush

@section('content')
<div style="margin-bottom:16px">
    <a href="{{ route('admin.users.index') }}?tab=walk-ins" class="btn btn-ghost btn-sm">← Back to Walk-in Directory</a>
</div>

@if(session('success'))
<div style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);border-radius:10px;padding:12px 16px;color:#4ade80;margin-bottom:16px;font-size:.9rem">
    ✓ {{ session('success') }}
</div>
@endif

{{-- Hero --}}
<div class="guest-hero">
    <div class="guest-av">{{ strtoupper(substr($guest->first_name,0,1).substr($guest->last_name,0,1)) }}</div>
    <div>
        <div style="font-size:1.3rem;font-weight:900;letter-spacing:-.04em">{{ $guest->full_name }}</div>
        <div style="font-size:.85rem;color:var(--muted)">Walk-in Guest · Since {{ $guest->created_at->format('M d, Y') }}</div>
        <div class="stat-chips">
            <span class="chip orange">{{ $guest->bookings->count() }} Booking(s)</span>
            @if($guest->phone)<span class="chip">📞 {{ $guest->phone }}</span>@endif
            @if($guest->email)<span class="chip">✉ {{ $guest->email }}</span>@endif
            @if($guest->drivers_license_number)<span class="chip">🪪 {{ $guest->drivers_license_number }}</span>@endif
        </div>
    </div>
    <div style="margin-left:auto">
        <a href="{{ route('admin.bookings.walk-in.create') }}" class="btn btn-primary btn-sm">
            + New Booking for this Guest
        </a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1.8fr;gap:20px;align-items:start">

    {{-- Edit Profile --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Edit Profile</span></div>
        <form method="POST" action="{{ route('admin.guests.update', $guest) }}">
            @csrf @method('PUT')
            <div class="card-body">
                <div class="g2">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $guest->first_name) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $guest->last_name) }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span style="font-size:.75rem;color:var(--muted)">(optional)</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $guest->email) }}" placeholder="Leave blank if none">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $guest->phone) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Driver's License #</label>
                    <input type="text" name="drivers_license_number" class="form-control" value="{{ old('drivers_license_number', $guest->drivers_license_number) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Internal Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. VIP, Blacklisted, etc.">{{ old('notes', $guest->notes) }}</textarea>
                </div>
            </div>
            <div style="padding:16px 22px;background:var(--ghost-bg);border-top:1px solid var(--line)">
                <button type="submit" class="btn btn-primary" style="width:100%">Save Changes</button>
            </div>
        </form>
    </div>

    {{-- Booking History --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Booking History</span>
            <span style="font-size:.82rem;color:var(--muted)">{{ $guest->bookings->count() }} total</span>
        </div>
        <div class="tw">
            <table>
                <thead><tr>
                    <th>#</th><th>Vehicle</th><th>Dates</th><th>Amount</th><th>Status</th><th></th>
                </tr></thead>
                <tbody>
                @forelse($guest->bookings as $b)
                <tr>
                    <td style="color:var(--muted);font-size:.82rem">#{{ $b->id }}</td>
                    <td>
                        <div style="font-weight:600">{{ $b->vehicle->name ?? '—' }}</div>
                        <div style="font-size:.75rem;color:var(--muted)">{{ $b->vehicle->plate_number ?? '' }}</div>
                    </td>
                    <td style="font-size:.82rem">
                        {{ $b->pickup_date->format('M d') }} → {{ $b->return_date->format('M d, Y') }}
                    </td>
                    <td style="font-weight:600;color:var(--orange-l)">₱{{ number_format($b->total_amount, 0) }}</td>
                    <td>
                        @php
                        $statusColors = [
                            'confirmed' => 'bg_', 'ongoing' => 'bo', 'completed' => 'bg_',
                            'cancelled' => 'br', 'no_show' => 'br', 'pending_payment' => 'bgy',
                            'fully_paid' => 'bg_', 'partial_paid' => 'bb',
                        ];
                        @endphp
                        <span class="badge {{ $statusColors[$b->status] ?? 'bgy' }}">
                            {{ ucwords(str_replace('_',' ', $b->status)) }}
                        </span>
                    </td>
                    <td><a href="{{ route('admin.bookings.show', $b) }}" class="btn btn-ghost btn-sm">View</a></td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:28px">No bookings yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
