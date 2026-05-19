@extends('layouts.app')
@section('title','Return Processing')
@section('page-title','Return Processing')
@section('content')
<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
        <div><span class="card-title">Active Rentals</span><span style="font-size:.85rem;color:var(--text-dim)">{{ $rentals->total() }} on the road</span></div>
        <a href="{{ route('admin.rentals.index') }}" class="btn btn-ghost btn-sm">View in Ongoing Rentals</a>
    </div>
    <div class="tw">
        <table>
            <thead><tr><th>Rental #</th><th>Customer</th><th>Vehicle</th><th>Started</th><th>Due Date</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($rentals as $r)
            <tr>
                <td style="color:var(--text-dim)">R-{{ str_pad($r->id, 5, '0', STR_PAD_LEFT) }}</td>
                <td><div style="font-weight:600">{{ $r->user?->name ?? $r->booking->name }}</div><div style="font-size:.8rem;color:var(--text-dim)">{{ $r->user?->phone ?? $r->booking->phone }}</div></td>
                <td>{{ $r->vehicle?->name }}</td>
                <td>{{ $r->pickup_date->format('M d, H:i') }}</td>
                <td>
                    @php $overdue = $r->isOverdue(); @endphp
                    <span style="color:{{ $overdue ? 'var(--red)' : 'var(--text)' }}">{{ $r->expected_return_date->format('M d, H:i') }}</span>
                    @if($overdue)<span class="badge br" style="margin-left:6px">Overdue</span>@endif
                </td>
                <td>
                    @php 
                        $hasReturnInsp = $r->booking->inspections()->where('type', 'return')->exists();
                    @endphp

                    @if(!$hasReturnInsp)
                        <a href="{{ route('admin.bookings.inspection.create', $r->booking) }}?type=return" class="btn btn-primary btn-sm">1. Record Return Inspection</a>
                    @else
                        <form method="POST" action="{{ route('admin.returns.process', $r) }}" onsubmit="return confirm('Process return for this rental?')">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">2. Complete Return & Close</button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-dim)">No active rentals at the moment.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($rentals->hasPages())<div style="padding:16px;border-top:1px solid var(--line)">{{ $rentals->links() }}</div>@endif
</div>
@endsection
