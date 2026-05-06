@extends('layouts.app')
@section('title','Vehicle Utilization Report')
@section('page-title','Vehicle Utilization')
@section('content')

<div class="flex" style="margin-bottom:20px;gap:10px">
    <a href="{{ route('admin.reports.index') }}" class="btn btn-ghost btn-sm">← Reports</a>
    <div class="ml-auto flex" style="gap:8px">
        <a href="{{ route('admin.reports.vehicles.csv', request()->query()) }}" class="btn btn-ghost btn-sm">↓ CSV</a>
    </div>
</div>

<div class="card" style="margin-bottom:16px">
    <div class="card-body" style="padding-top:16px;padding-bottom:16px">
        <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <label style="font-size:.82rem;color:var(--muted)">From</label>
            <input type="date" name="date_from" value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}" class="form-control" style="width:160px">
            <label style="font-size:.82rem;color:var(--muted)">To</label>
            <input type="date" name="date_to"   value="{{ request('date_to', $dateTo->format('Y-m-d')) }}"   class="form-control" style="width:160px">
            <button type="submit" class="btn btn-primary">Apply</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="tw">
        <table>
            <thead><tr>
                <th>Vehicle</th><th>Brand / Model</th><th>Year</th>
                <th>Days Rented</th><th>Utilization %</th><th>Revenue (₱)</th>
            </tr></thead>
            <tbody>
            @forelse($vehicles as $v)
            <tr>
                <td style="font-weight:700">{{ $v->name }}</td>
                <td style="font-size:.88rem;color:var(--muted)">{{ $v->brand }} {{ $v->model }}</td>
                <td style="font-size:.85rem">{{ $v->year }}</td>
                <td>{{ $v->days_rented }}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px">
                        <div style="flex:1;height:6px;background:rgba(255,255,255,.08);border-radius:99px;overflow:hidden">
                            <div style="height:100%;width:{{ min($v->utilization_pct,100) }}%;background:linear-gradient(90deg,#ff6b00,#ff8c3a);border-radius:99px"></div>
                        </div>
                        <span style="font-size:.85rem;font-weight:700;color:var(--orange-l)">{{ $v->utilization_pct }}%</span>
                    </div>
                </td>
                <td style="font-weight:700;color:var(--orange-l)">₱{{ number_format($v->total_revenue,0) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:32px">No vehicle data.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
