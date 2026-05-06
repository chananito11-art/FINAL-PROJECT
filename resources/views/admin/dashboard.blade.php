@extends('layouts.app')
@section('title','Dashboard')
@section('page-title','Dashboard')
@push('styles')
<style>
.stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-bottom:24px}
.stat-card{background:var(--card-bg);border:1px solid var(--line);border-radius:16px;padding:20px;position:relative;overflow:hidden;transition:border-color .2s}
.stat-card:hover{border-color:rgba(255,107,0,.25)}
.stat-icon{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;margin-bottom:12px}
.stat-label{font-size:.78rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px}
.stat-value{font-size:1.9rem;font-weight:900;letter-spacing:-.04em;line-height:1}
.stat-sub{font-size:.78rem;color:var(--muted);margin-top:4px}
.charts-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px}
.quick-actions{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:24px}
.qa-btn{display:flex;align-items:center;gap:8px;padding:10px 18px;border-radius:12px;border:1px solid var(--line);background:var(--card-bg);color:var(--text);font-family:inherit;font-size:.88rem;font-weight:600;cursor:pointer;text-decoration:none;transition:all .15s}
.qa-btn:hover{border-color:rgba(255,107,0,.4);background:rgba(255,107,0,.06)}
.qa-badge{font-size:.72rem;font-weight:800;padding:2px 8px;border-radius:20px;background:rgba(255,107,0,.2);color:var(--orange-l)}
.activity-item{display:flex;gap:12px;padding:10px 0;border-bottom:1px solid var(--line)}
.activity-item:last-child{border-bottom:none}
.activity-dot{width:8px;height:8px;border-radius:50%;background:var(--orange);margin-top:6px;flex-shrink:0}
.activity-text{font-size:.86rem;line-height:1.5}
.activity-meta{font-size:.76rem;color:var(--muted);margin-top:2px}
</style>
@endpush
@section('content')

{{-- Quick Actions --}}
<div class="quick-actions">
    <a href="{{ route('admin.bookings.index') }}?status=awaiting_approval" class="qa-btn" style="border-color:rgba(59,130,246,.4);background:rgba(59,130,246,.05)">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Awaiting Approval
        <span class="qa-badge" style="background:rgba(59,130,246,.2);color:#3b82f6">{{ $quickCounts['awaiting_approval'] }}</span>
    </a>
    <a href="{{ route('admin.payments.index') }}?tab=pending" class="qa-btn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        Verify Pending Payments
        <span class="qa-badge">{{ $quickCounts['pending_payments'] }}</span>
    </a>
    <a href="{{ route('admin.bookings.index') }}?status=awaiting_verification" class="qa-btn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Awaiting Verification
        <span class="qa-badge">{{ $quickCounts['awaiting_verify'] }}</span>
    </a>
    <a href="{{ route('admin.returns.index') }}" class="qa-btn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.56"/></svg>
        Returns Due Today
        <span class="qa-badge">{{ $quickCounts['returns_today'] }}</span>
    </a>
    <a href="{{ route('admin.users.index') }}?tab=employees" class="qa-btn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        Manage Employees
    </a>
</div>

{{-- Stat Cards --}}
<div class="stat-grid">
    <div class="stat-card" style="border-color:rgba(255,107,0,.2);background:rgba(255,107,0,.05)">
        <div class="stat-icon" style="background:rgba(255,107,0,.15)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ff8c3a" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value" style="color:var(--orange-l)">₱{{ number_format($stats['total_revenue'],0) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(34,197,94,.1)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="stat-label">Revenue This Month</div>
        <div class="stat-value" style="color:var(--green)">₱{{ number_format($stats['revenue_this_month'],0) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(59,130,246,.1)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><path d="M3 13l1.5-4.5A3 3 0 0 1 7.35 6h9.3a3 3 0 0 1 2.85 2.5L21 13"/><path d="M5 16h14"/><circle cx="7.5" cy="15.5" r="1.5"/><circle cx="16.5" cy="15.5" r="1.5"/></svg>
        </div>
        <div class="stat-label">Total Vehicles</div>
        <div class="stat-value">{{ $stats['total_vehicles'] }}</div>
        <div class="stat-sub">{{ $stats['available_vehicles'] }} available</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(34,197,94,.1)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><path d="M3 13l1.5-4.5A3 3 0 0 1 7.35 6h9.3a3 3 0 0 1 2.85 2.5L21 13"/><circle cx="7.5" cy="15.5" r="1.5"/><circle cx="16.5" cy="15.5" r="1.5"/></svg>
        </div>
        <div class="stat-label">Available Vehicles</div>
        <div class="stat-value" style="color:var(--green)">{{ $stats['available_vehicles'] }}</div>
    </div>
    <a href="{{ route('admin.users.index') }}?tab=customers" style="text-decoration:none;color:inherit">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(168,85,247,.1)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div class="stat-label">Total Customers</div>
            <div class="stat-value">{{ $stats['total_customers'] }}</div>
        </div>
    </a>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(255,107,0,.1)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ff8c3a" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div class="stat-label">Active Bookings</div>
        <div class="stat-value" style="color:var(--orange-l)">{{ $stats['active_bookings'] }}</div>
        <div class="stat-sub">confirmed + ongoing</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(59,130,246,.1)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="stat-label">Pending Verification</div>
        <div class="stat-value" style="color:var(--blue)">{{ $stats['pending_verification'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(239,68,68,.1)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div class="stat-label">Cancelled This Month</div>
        <div class="stat-value" style="color:var(--red)">{{ $stats['cancelled_this_month'] }}</div>
    </div>
</div>

{{-- Charts --}}
<div class="charts-grid">
    <div class="card">
        <div class="card-header"><span class="card-title">Revenue (Last 6 Months)</span></div>
        <div class="card-body"><canvas id="revenueChart" height="200"></canvas></div>
    </div>
    <div class="card">
        <div class="card-header"><span class="card-title">Bookings by Status</span></div>
        <div class="card-body"><canvas id="bookingChart" height="200"></canvas></div>
    </div>
    <div class="card">
        <div class="card-header"><span class="card-title">Top 5 Vehicles</span></div>
        <div class="card-body"><canvas id="vehicleChart" height="200"></canvas></div>
    </div>
    <div class="card">
        <div class="card-header"><span class="card-title">Booking Activity (30 Days)</span></div>
        <div class="card-body"><canvas id="timelineChart" height="200"></canvas></div>
    </div>
</div>

{{-- Activity Feed --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">Recent Activity</span>
        @if(auth()->user()->isSuperAdmin())
        <a href="{{ route('super-admin.logs.index') }}" class="btn btn-ghost btn-sm">View All Logs</a>
        @endif
    </div>
    <div class="card-body">
        @forelse($recentActivity as $log)
        <div class="activity-item">
            <div class="activity-dot"></div>
            <div>
                <div class="activity-text">{{ $log->action }}</div>
                <div class="activity-meta">
                    {{ $log->user?->first_name ?? 'System' }} · {{ $log->created_at->diffForHumans() }}
                </div>
            </div>
        </div>
        @empty
        <p style="color:var(--muted);font-size:.9rem">No activity yet.</p>
        @endforelse
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const getChartTheme = () => {
    const isLight = document.body.classList.contains('light-mode');
    const color = isLight ? '#111827' : '#f0f2ff';
    const muted = isLight ? 'rgba(17,24,39,0.6)' : 'rgba(240,242,255,0.55)';
    const dim = isLight ? 'rgba(17,24,39,0.4)' : 'rgba(240,242,255,0.4)';
    const grid = isLight ? 'rgba(0,0,0,0.05)' : 'rgba(255,255,255,0.05)';
    
    return {
        color: color,
        plugins: { legend: { labels: { color: muted, font: { size: 12 } } } },
        scales: {
            x: { ticks: { color: dim }, grid: { color: grid } },
            y: { ticks: { color: dim }, grid: { color: grid } }
        }
    };
};

async function loadChart(endpoint, canvasId, buildFn) {
    try {
        const res = await fetch(endpoint);
        const data = await res.json();
        const ctx = document.getElementById(canvasId).getContext('2d');
        buildFn(ctx, data);
    } catch(e) { console.error('Chart load error:', e); }
}

// Revenue Chart
loadChart('{{ route("admin.dashboard.revenue-chart") }}', 'revenueChart', (ctx, d) => {
    new Chart(ctx, { type: 'bar', data: {
        labels: d.labels,
        datasets: [{ label: 'Revenue (₱)', data: d.data, backgroundColor: 'rgba(255,107,0,0.5)', borderColor: '#ff6b00', borderWidth: 2, borderRadius: 6 }]
    }, options: { ...getChartTheme(), plugins: { ...getChartTheme().plugins, legend: { display: false } } } });
});

// Booking Status Doughnut
loadChart('{{ route("admin.dashboard.booking-chart") }}', 'bookingChart', (ctx, d) => {
    new Chart(ctx, { type: 'doughnut', data: {
        labels: d.labels,
        datasets: [{ data: d.data, backgroundColor: ['#3b82f6','#f59e0b','#3b82f6','#22c55e','#ff6b00','#6b7280','#ef4444','#dc2626'], borderWidth: 0 }]
    }, options: { plugins: { legend: { labels: { color: getChartTheme().plugins.legend.labels.color, font: { size: 11 } } } }, cutout: '65%' } });
});

// Vehicle Bar
loadChart('{{ route("admin.dashboard.vehicle-chart") }}', 'vehicleChart', (ctx, d) => {
    new Chart(ctx, { type: 'bar', data: {
        labels: d.labels,
        datasets: [{ label: 'Bookings', data: d.data, backgroundColor: 'rgba(59,130,246,0.4)', borderColor: '#3b82f6', borderWidth: 2, borderRadius: 6 }]
    }, options: { ...getChartTheme(), indexAxis: 'y', plugins: { ...getChartTheme().plugins, legend: { display: false } } } });
});

// Timeline Line
loadChart('{{ route("admin.dashboard.booking-timeline") }}', 'timelineChart', (ctx, d) => {
    new Chart(ctx, { type: 'line', data: {
        labels: d.labels,
        datasets: [{ label: 'Bookings', data: d.data, borderColor: '#ff8c3a', backgroundColor: 'rgba(255,107,0,0.1)', borderWidth: 2, tension: 0.4, fill: true, pointRadius: 3 }]
    }, options: { ...getChartTheme(), plugins: { ...getChartTheme().plugins, legend: { display: false } } } });
});
</script>
@endpush
