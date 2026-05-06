@extends('layouts.app')
@section('title','Reports')
@section('page-title','Reports')
@section('content')
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px">

    <a href="{{ route('admin.reports.revenue') }}" style="text-decoration:none">
        <div class="card" style="padding:28px;cursor:pointer;transition:border-color .2s;border-color:rgba(255,107,0,.15)" onmouseover="this.style.borderColor='rgba(255,107,0,.4)'" onmouseout="this.style.borderColor='rgba(255,107,0,.15)'">
            <div style="width:44px;height:44px;border-radius:12px;background:rgba(255,107,0,.15);display:grid;place-items:center;margin-bottom:14px">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ff8c3a" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div style="font-size:1.05rem;font-weight:800;margin-bottom:6px">Revenue Report</div>
            <div style="font-size:.85rem;color:var(--muted)">Monthly breakdown, revenue by vehicle, date range filter.</div>
            <div style="margin-top:14px;display:flex;gap:8px">
                <span class="badge bo">PDF</span><span class="badge bgy">CSV</span>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.reports.bookings') }}" style="text-decoration:none">
        <div class="card" style="padding:28px;cursor:pointer;transition:border-color .2s;border-color:rgba(59,130,246,.15)" onmouseover="this.style.borderColor='rgba(59,130,246,.4)'" onmouseout="this.style.borderColor='rgba(59,130,246,.15)'">
            <div style="width:44px;height:44px;border-radius:12px;background:rgba(59,130,246,.15);display:grid;place-items:center;margin-bottom:14px">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div style="font-size:1.05rem;font-weight:800;margin-bottom:6px">Booking Report</div>
            <div style="font-size:.85rem;color:var(--muted)">Filter by status, vehicle, customer, or date range.</div>
            <div style="margin-top:14px;display:flex;gap:8px">
                <span class="badge bb">PDF</span><span class="badge bgy">CSV</span>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.reports.vehicles') }}" style="text-decoration:none">
        <div class="card" style="padding:28px;cursor:pointer;transition:border-color .2s;border-color:rgba(34,197,94,.15)" onmouseover="this.style.borderColor='rgba(34,197,94,.4)'" onmouseout="this.style.borderColor='rgba(34,197,94,.15)'">
            <div style="width:44px;height:44px;border-radius:12px;background:rgba(34,197,94,.15);display:grid;place-items:center;margin-bottom:14px">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><path d="M3 13l1.5-4.5A3 3 0 0 1 7.35 6h9.3a3 3 0 0 1 2.85 2.5L21 13"/><circle cx="7.5" cy="15.5" r="1.5"/><circle cx="16.5" cy="15.5" r="1.5"/></svg>
            </div>
            <div style="font-size:1.05rem;font-weight:800;margin-bottom:6px">Vehicle Utilization</div>
            <div style="font-size:.85rem;color:var(--muted)">Days rented, revenue, and utilization % per vehicle.</div>
            <div style="margin-top:14px"><span class="badge bg_">CSV</span></div>
        </div>
    </a>

    <a href="{{ route('admin.reports.customers') }}" style="text-decoration:none">
        <div class="card" style="padding:28px;cursor:pointer;transition:border-color .2s;border-color:rgba(168,85,247,.15)" onmouseover="this.style.borderColor='rgba(168,85,247,.4)'" onmouseout="this.style.borderColor='rgba(168,85,247,.15)'">
            <div style="width:44px;height:44px;border-radius:12px;background:rgba(168,85,247,.15);display:grid;place-items:center;margin-bottom:14px">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div style="font-size:1.05rem;font-weight:800;margin-bottom:6px">Customer Report</div>
            <div style="font-size:.85rem;color:var(--muted)">Booking count, total spent, last booking per customer.</div>
            <div style="margin-top:14px"><span class="badge bgy">CSV</span></div>
        </div>
    </a>

</div>
@endsection
