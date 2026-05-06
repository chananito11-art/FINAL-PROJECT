@extends('layouts.app')
@section('title','Customer Report')
@section('page-title','Customer Report')
@section('content')

<div class="flex" style="margin-bottom:20px;gap:10px">
    <a href="{{ route('admin.reports.index') }}" class="btn btn-ghost btn-sm">← Reports</a>
    <a href="{{ route('admin.reports.customers.csv') }}" class="btn btn-ghost btn-sm ml-auto">↓ CSV</a>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">All Customers</span>
        <span style="font-size:.82rem;color:var(--muted)">{{ $customers->count() }} customers</span>
    </div>
    <div class="tw">
        <table>
            <thead><tr>
                <th>#</th><th>Name</th><th>Email</th><th>Status</th>
                <th>Bookings</th><th>Total Spent</th><th>Last Booking</th><th>Joined</th>
            </tr></thead>
            <tbody>
            @forelse($customers as $c)
            <tr>
                <td style="color:var(--muted);font-size:.82rem">#{{ $c->id }}</td>
                <td style="font-weight:600">{{ $c->first_name }} {{ $c->last_name }}</td>
                <td style="font-size:.85rem;color:var(--muted)">{{ $c->email }}</td>
                <td>
                    @if($c->status === 'active') <span class="badge bg_">Active</span>
                    @else <span class="badge br">Suspended</span> @endif
                </td>
                <td>{{ $c->bookings_count }}</td>
                <td style="font-weight:700;color:var(--orange-l)">₱{{ number_format($c->total_spent,0) }}</td>
                <td style="font-size:.82rem;color:var(--muted)">
                    {{ $c->last_booking ? $c->last_booking->created_at->format('M d, Y') : '—' }}
                </td>
                <td style="font-size:.82rem;color:var(--muted)">{{ $c->created_at->format('M d, Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:32px">No customers found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
