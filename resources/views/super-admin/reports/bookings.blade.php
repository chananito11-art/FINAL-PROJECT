@extends('layouts.app')
@section('title','Booking Report')
@section('page-title','Booking Report')
@section('content')

<div class="flex" style="margin-bottom:20px;gap:10px">
    <a href="{{ route('admin.reports.index') }}" class="btn btn-ghost btn-sm">← Reports</a>
    <div class="ml-auto flex" style="gap:8px">
        <a href="{{ route('admin.reports.bookings.pdf', request()->query()) }}" class="btn btn-danger btn-sm">↓ PDF</a>
        <a href="{{ route('admin.reports.bookings.csv', request()->query()) }}" class="btn btn-ghost btn-sm">↓ CSV</a>
    </div>
</div>

<div class="card" style="margin-bottom:16px">
    <div class="card-body" style="padding-top:16px;padding-bottom:16px">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <select name="status" class="form-control" style="width:180px">
                <option value="">All Statuses</option>
                @foreach(['pending_payment','awaiting_verification','confirmed','ongoing','completed','rejected','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
            <select name="vehicle_id" class="form-control" style="width:180px">
                <option value="">All Vehicles</option>
                @foreach($vehicles as $v)
                <option value="{{ $v->id }}" {{ request('vehicle_id')==$v->id?'selected':'' }}>{{ $v->name }}</option>
                @endforeach
            </select>
            <input type="text"  name="customer"  value="{{ request('customer') }}"  placeholder="Customer name/email" class="form-control" style="width:180px">
            <input type="date"  name="date_from" value="{{ request('date_from') }}" class="form-control" style="width:145px">
            <input type="date"  name="date_to"   value="{{ request('date_to') }}"   class="form-control" style="width:145px">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.reports.bookings') }}" class="btn btn-ghost">Reset</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Bookings</span>
        <span style="font-size:.82rem;color:var(--muted)">{{ $bookings->total() }} results</span>
    </div>
    <div class="tw">
        <table>
            <thead><tr>
                <th>#</th><th>Customer</th><th>Vehicle</th><th>Pickup</th><th>Return</th><th>Amount</th><th>Status</th><th>Created</th>
            </tr></thead>
            <tbody>
            @forelse($bookings as $b)
            @php $badges=['pending_payment'=>'by','awaiting_verification'=>'bb','confirmed'=>'bg_','rejected'=>'br','ongoing'=>'bo','completed'=>'bgy','cancelled'=>'br']; @endphp
            <tr>
                <td style="color:var(--muted);font-size:.82rem">#{{ $b->id }}</td>
                <td style="font-weight:600;font-size:.88rem">{{ $b->user?->first_name }} {{ $b->user?->last_name }}</td>
                <td style="font-size:.88rem;color:var(--muted)">{{ $b->vehicle?->name }}</td>
                <td style="font-size:.85rem">{{ $b->pickup_date?->format('M d, Y') }}</td>
                <td style="font-size:.85rem">{{ $b->return_date?->format('M d, Y') }}</td>
                <td style="font-weight:700;color:var(--orange-l)">₱{{ number_format($b->total_amount,0) }}</td>
                <td><span class="badge {{ $badges[$b->status]??'bgy' }}">{{ ucwords(str_replace('_',' ',$b->status)) }}</span></td>
                <td style="font-size:.82rem;color:var(--muted)">{{ $b->created_at?->format('M d') }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:32px">No bookings match the filter.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($bookings->hasPages())
    <div style="padding:16px 22px;border-top:1px solid var(--line)">{{ $bookings->links() }}</div>
    @endif
</div>
@endsection
