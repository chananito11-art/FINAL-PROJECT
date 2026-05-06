@extends('layouts.app')
@section('title','Bookings')
@section('page-title','Bookings')
@section('content')
@push('styles')
<style>
.filter-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px}
.filter-tab{padding:7px 16px;border-radius:999px;font-size:.82rem;font-weight:600;text-decoration:none;color:var(--muted);background:var(--ghost-bg);border:1px solid var(--line);transition:all .2s}
.filter-tab:hover,.filter-tab.active{background:rgba(255,107,0,.15);color:#ff8c3a;border-color:rgba(255,107,0,.25)}
</style>
@endpush
<div class="filter-tabs">
    <a href="{{ route('admin.bookings.index') }}" class="filter-tab {{ !$status?'active':'' }}">All</a>
    @foreach(['awaiting_approval','pending_payment','awaiting_verification','confirmed','ongoing','completed','rejected','cancelled'] as $s)
    <a href="{{ route('admin.bookings.index',['status'=>$s]) }}" class="filter-tab {{ $status===$s?'active':'' }}">{{ ucwords(str_replace('_',' ',$s)) }}</a>
    @endforeach
</div>
<div class="card">
    <div class="card-header">
        <span class="card-title">{{ $status ? ucwords(str_replace('_',' ',$status)) : 'All' }} Bookings</span>
        <span style="font-size:.85rem;color:var(--text-dim)">{{ $bookings->total() }} total</span>
    </div>
    <div class="tw">
        <table>
            <thead><tr><th>#</th><th>Customer</th><th>Vehicle</th><th>Pickup</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($bookings as $b)
            <tr>
                <td style="color:var(--text-dim);font-size:.82rem">#{{ $b->id }}</td>
                <td>
                    <div style="font-weight:600">{{ $b->first_name }} {{ $b->last_name }}</div>
                    <div style="font-size:.8rem;color:var(--text-dim)">{{ $b->email }}</div>
                </td>
                <td>{{ $b->vehicle?->name }}</td>
                <td style="font-size:.85rem">{{ $b->pickup_date?->format('M d, Y') }}</td>
                <td style="color:#ff8c3a;font-weight:700">₱{{ number_format($b->total_amount,0) }}</td>
                <td>
                    @php $map=['awaiting_approval'=>'bb','pending_payment'=>'by','awaiting_verification'=>'bb','confirmed'=>'bg_','rejected'=>'br','ongoing'=>'bo','completed'=>'bgy','cancelled'=>'br']; @endphp
                    <span class="badge {{ $map[$b->status]??'bgy' }}">{{ ucwords(str_replace('_',' ',$b->status)) }}</span>
                </td>
                <td><a href="{{ route('admin.bookings.show',$b) }}" class="btn btn-ghost btn-sm">View</a></td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-dim)">No bookings found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($bookings->hasPages())
    <div style="padding:16px;border-top:1px solid var(--line)">{{ $bookings->links() }}</div>
    @endif
</div>
@endsection
