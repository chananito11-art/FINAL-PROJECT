@extends('layouts.app')
@section('title','Dashboard')
@section('page-title','Dashboard')
@section('content')
<div class="stat-grid">
    <div class="stat-card orange">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value">₱{{ number_format($stats['revenue'],0) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Vehicles</div>
        <div class="stat-value">{{ $stats['vehicles'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Available</div>
        <div class="stat-value" style="color:#4ade80">{{ $stats['available_vehicles'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Bookings</div>
        <div class="stat-value">{{ $stats['bookings'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pending Verify</div>
        <div class="stat-value" style="color:#60a5fa">{{ $stats['pending_verification'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Ongoing</div>
        <div class="stat-value" style="color:#ff8c3a">{{ $stats['ongoing'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Customers</div>
        <div class="stat-value">{{ $stats['customers'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Confirmed</div>
        <div class="stat-value" style="color:#4ade80">{{ $stats['confirmed'] }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Recent Bookings</span>
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-ghost btn-sm">View All</a>
    </div>
    <div class="tw">
        <table>
            <thead><tr>
                <th>#</th><th>Customer</th><th>Vehicle</th><th>Pickup</th><th>Return</th><th>Amount</th><th>Status</th><th></th>
            </tr></thead>
            <tbody>
            @forelse($recentBookings as $b)
            <tr>
                <td style="color:rgba(240,242,255,.45);font-size:.82rem">#{{ $b->id }}</td>
                <td>{{ $b->user?->first_name }} {{ $b->user?->last_name }}</td>
                <td>{{ $b->vehicle?->name }}</td>
                <td style="font-size:.85rem">{{ $b->pickup_date?->format('M d, Y') }}</td>
                <td style="font-size:.85rem">{{ $b->return_date?->format('M d, Y') }}</td>
                <td style="color:#ff8c3a;font-weight:700">₱{{ number_format($b->total_amount,0) }}</td>
                <td>
                    @php $badge=['pending_payment'=>'by','awaiting_verification'=>'bb','confirmed'=>'bg_','rejected'=>'br','ongoing'=>'bo','completed'=>'bgy','cancelled'=>'br'][$b->status]??'bgy'; @endphp
                    <span class="badge {{ $badge }}">{{ ucwords(str_replace('_',' ',$b->status)) }}</span>
                </td>
                <td><a href="{{ route('admin.bookings.show', $b) }}" class="btn btn-ghost btn-sm">View</a></td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;color:rgba(240,242,255,.4);padding:32px">No bookings yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
