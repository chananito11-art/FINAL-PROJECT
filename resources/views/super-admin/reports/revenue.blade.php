@extends('layouts.app')
@section('title','Revenue Report')
@section('page-title','Revenue Report')
@section('content')

<div class="flex" style="margin-bottom:20px;flex-wrap:wrap;gap:10px">
    <a href="{{ route('admin.reports.index') }}" class="btn btn-ghost btn-sm">← Reports</a>
    <div class="ml-auto flex" style="gap:8px">
        <a href="{{ route('admin.reports.revenue.pdf', request()->query()) }}" class="btn btn-danger btn-sm">↓ PDF</a>
        <a href="{{ route('admin.reports.revenue.csv', request()->query()) }}" class="btn btn-ghost btn-sm">↓ CSV</a>
    </div>
</div>

<div class="card" style="margin-bottom:16px">
    <div class="card-body" style="padding-top:16px;padding-bottom:16px">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <label style="font-size:.82rem;color:var(--muted)">From</label>
            <input type="date" name="date_from" value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}" class="form-control" style="width:160px">
            <label style="font-size:.82rem;color:var(--muted)">To</label>
            <input type="date" name="date_to"   value="{{ request('date_to',   $dateTo->format('Y-m-d')) }}"   class="form-control" style="width:160px">
            <button type="submit" class="btn btn-primary">Apply</button>
        </form>
    </div>
</div>

<div class="stat-grid" style="margin-bottom:20px">
    <div class="stat-card orange">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value">₱{{ number_format($totalRevenue,0) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Months Covered</div>
        <div class="stat-value">{{ $monthly->count() }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Payments</div>
        <div class="stat-value">{{ $monthly->sum('count') }}</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    <div class="card">
        <div class="card-header"><span class="card-title">Monthly Breakdown</span></div>
        <div class="tw">
            <table>
                <thead><tr><th>Month</th><th>Payments</th><th>Revenue</th></tr></thead>
                <tbody>
                @forelse($monthly as $row)
                <tr>
                    <td>{{ date('F Y', strtotime($row->month . '-01')) }}</td>
                    <td>{{ $row->count }}</td>
                    <td style="font-weight:700;color:var(--orange-l)">₱{{ number_format($row->total,0) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align:center;color:var(--muted);padding:24px">No revenue data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span class="card-title">Top 10 Vehicles by Revenue</span></div>
        <div class="tw">
            <table>
                <thead><tr><th>Vehicle</th><th>Bookings</th><th>Revenue</th></tr></thead>
                <tbody>
                @forelse($byVehicle as $name => $data)
                <tr>
                    <td style="font-weight:600">{{ $name }}</td>
                    <td>{{ $data['count'] }}</td>
                    <td style="font-weight:700;color:var(--orange-l)">₱{{ number_format($data['total'],0) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align:center;color:var(--muted);padding:24px">No data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
