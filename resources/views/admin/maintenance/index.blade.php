@extends('layouts.app')
@section('title', 'Vehicle Maintenance')
@section('page-title', 'Preventive Maintenance (PM) Tracking')

@push('styles')
<style>
    .pm-alert{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:12px;padding:16px;margin-bottom:20px;display:flex;align-items:center;gap:16px}
    .pm-alert svg{color:#f87171;flex-shrink:0}
    .g2{display:grid;grid-template-columns:1fr 1fr;gap:13px}
    .fleet-card{background:rgba(255,255,255,.03);padding:14px 16px;border-radius:12px;border:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;gap:12px}
    .fleet-card.maintenance{border-color:rgba(239,68,68,.3);background:rgba(239,68,68,.05)}
    .checklist-item{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--line);font-size:.88rem}
    .checklist-item:last-child{border-bottom:none}
    .checklist-item input[type=checkbox]{accent-color:#ff6b00;width:16px;height:16px;flex-shrink:0}
    .status-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
    .dot-available{background:#4ade80}
    .dot-rented{background:#60a5fa}
    .dot-unavailable{background:#f87171}
</style>
@endpush

@section('content')

{{-- Flash messages --}}
@if(session('success'))
<div style="background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.25);border-radius:10px;padding:12px 16px;color:#4ade80;margin-bottom:16px;font-size:.9rem">
    ✓ {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);border-radius:10px;padding:12px 16px;color:#f87171;margin-bottom:16px;font-size:.9rem">
    ✕ {{ session('error') }}
</div>
@endif

{{-- Upcoming PM Alert --}}
@if($upcomingPM->count() > 0)
<div class="pm-alert">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <div>
        <div style="font-weight:700;color:#f87171">⚠ Maintenance Due Soon</div>
        <div style="font-size:.85rem;color:var(--muted)">{{ $upcomingPM->count() }} vehicle(s) are approaching their next scheduled service.</div>
    </div>
</div>
@endif

{{-- Fleet Status Bar --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <span class="card-title">Fleet Status</span>
        <span style="font-size:.82rem;color:var(--muted)">{{ $vehicles->count() }} total vehicles</span>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px">
            @foreach($vehicles as $v)
            <div class="fleet-card {{ $v->status === 'unavailable' ? 'maintenance' : '' }}">
                <div>
                    <div style="font-weight:700;font-size:.88rem">{{ $v->name }}</div>
                    <div style="font-size:.75rem;color:var(--muted)">{{ $v->plate_number }} · {{ number_format($v->odometer) }} km</div>
                </div>
                <div style="display:flex;align-items:center;gap:6px">
                    <div class="status-dot dot-{{ $v->status === 'rented' ? 'rented' : ($v->status === 'unavailable' ? 'unavailable' : 'available') }}"></div>
                    <span style="font-size:.78rem;font-weight:600;color:{{ $v->status === 'unavailable' ? '#f87171' : ($v->status === 'rented' ? '#60a5fa' : '#4ade80') }}">
                        {{ $v->status === 'unavailable' ? 'Maintenance' : ucfirst($v->status) }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1.6fr;gap:24px;align-items:start">

    {{-- Left Column --}}
    <div style="display:flex;flex-direction:column;gap:20px">

        {{-- Log New Service --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Log Maintenance Service</span></div>
            <form action="{{ route('admin.maintenance.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Vehicle</label>
                        <select name="vehicle_id" class="form-control" required>
                            <option value="">-- Select Vehicle --</option>
                            @foreach($vehicles as $v)
                            <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->plate_number }}) — {{ number_format($v->odometer) }} km</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Service Type</label>
                        <select name="service_type" class="form-control" required>
                            <option>Oil Change</option>
                            <option>Tire Rotation</option>
                            <option>Brake Inspection</option>
                            <option>General Checkup / PM</option>
                            <option>Engine Tune-up</option>
                            <option>Air Filter Replacement</option>
                            <option>Battery Check</option>
                            <option>Transmission Service</option>
                            <option>Cooling System Flush</option>
                            <option>Other</option>
                        </select>
                    </div>

                    {{-- PM Checklist --}}
                    <div style="border:1px solid var(--line);border-radius:10px;padding:14px;margin-bottom:14px">
                        <div style="font-size:.78rem;font-weight:700;text-transform:uppercase;color:var(--muted);letter-spacing:.05em;margin-bottom:10px">PM Checklist</div>
                        @foreach([
                            'pm_oil'         => 'Engine Oil & Filter',
                            'pm_brakes'      => 'Brake Pads & Fluid',
                            'pm_tires'       => 'Tire Condition & Pressure',
                            'pm_lights'      => 'Lights & Signals',
                            'pm_battery'     => 'Battery & Terminals',
                            'pm_airfilter'   => 'Air Filter',
                            'pm_coolant'     => 'Coolant Level',
                            'pm_wipers'      => 'Windshield & Wipers',
                            'pm_belts'       => 'Belts & Hoses',
                            'pm_exterior'    => 'Exterior & Damage Check',
                        ] as $name => $label)
                        <div class="checklist-item">
                            <input type="checkbox" name="checklist[]" id="{{ $name }}" value="{{ $label }}">
                            <label for="{{ $name }}" style="cursor:pointer;flex:1">{{ $label }}</label>
                        </div>
                        @endforeach
                    </div>

                    <div class="g2">
                        <div class="form-group">
                            <label class="form-label">Mileage at Service (km)</label>
                            <input type="number" name="mileage_at_service" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Service Date</label>
                            <input type="date" name="service_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cost (PHP)</label>
                        <input type="number" step="0.01" name="cost" class="form-control" placeholder="0.00">
                    </div>

                    <div style="margin:16px 0;padding-top:16px;border-top:1px solid var(--line)">
                        <div style="font-size:.78rem;font-weight:700;text-transform:uppercase;color:var(--muted);letter-spacing:.05em;margin-bottom:10px">Next Service Reminder</div>
                        <div class="g2">
                            <div class="form-group">
                                <label class="form-label">Due at Mileage (km)</label>
                                <input type="number" name="next_service_due_mileage" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="next_service_due_date" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Performed By / Workshop</label>
                        <input type="text" name="performed_by" class="form-control" placeholder="e.g. Toyota Service Center">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notes / Findings</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div style="padding:16px 22px;background:var(--ghost-bg);border-top:1px solid var(--line)">
                    <button type="submit" class="btn btn-primary" style="width:100%">Record Maintenance</button>
                </div>
            </form>
        </div>

        {{-- Send Vehicle to Maintenance --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Vehicle Status Control</span></div>
            <div class="card-body">
                <p style="font-size:.85rem;color:var(--muted);margin-bottom:16px">
                    Lock a vehicle from new bookings while it's being serviced, then release it when done.
                </p>
                {{-- Send to Maintenance --}}
                @foreach($vehicles->where('status', '!=', 'unavailable') as $v)
                <form method="POST" action="{{ route('admin.maintenance.send', $v) }}" style="display:flex;gap:8px;align-items:center;margin-bottom:10px">
                    @csrf
                    <div style="flex:1;font-size:.88rem;font-weight:600">{{ $v->name }}</div>
                    <input type="text" name="reason" class="form-control" placeholder="Reason" style="flex:2;height:36px" required>
                    <button class="btn btn-danger btn-sm" style="white-space:nowrap" title="Send to Maintenance">🔧 Lock</button>
                </form>
                @endforeach

                {{-- Release from Maintenance --}}
                @php $underMaintenance = $vehicles->where('status', 'unavailable'); @endphp
                @if($underMaintenance->count() > 0)
                <div style="border-top:1px solid var(--line);padding-top:14px;margin-top:14px">
                    <div style="font-size:.78rem;font-weight:700;color:#f87171;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Currently Under Maintenance</div>
                    @foreach($underMaintenance as $v)
                    <form method="POST" action="{{ route('admin.maintenance.release', $v) }}" style="display:flex;gap:8px;align-items:center;margin-bottom:10px">
                        @csrf
                        <div style="flex:1;font-size:.88rem;font-weight:600;color:#f87171">{{ $v->name }}</div>
                        <button class="btn btn-success btn-sm" onclick="return confirm('Release {{ $v->name }} and mark as Available?')">✓ Release</button>
                    </form>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right Column --}}
    <div style="display:flex;flex-direction:column;gap:20px">

        {{-- Recent Logs --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Recent Maintenance Logs</span></div>
            <div class="tw">
                <table>
                    <thead><tr><th>Vehicle</th><th>Service</th><th>Mileage</th><th>Date</th><th>Cost</th><th></th></tr></thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>
                                <div style="font-weight:600">{{ $log->vehicle->name }}</div>
                                <div style="font-size:.75rem;color:var(--muted)">{{ $log->vehicle->plate_number }}</div>
                            </td>
                            <td>
                                <div>{{ $log->service_type }}</div>
                                @if($log->description)
                                <div style="font-size:.75rem;color:var(--muted);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $log->description }}</div>
                                @endif
                            </td>
                            <td>{{ number_format($log->mileage_at_service) }} km</td>
                            <td style="font-size:.82rem">{{ $log->service_date->format('M d, Y') }}</td>
                            <td style="font-weight:600;color:var(--orange-l)">₱{{ number_format($log->cost ?? 0, 0) }}</td>
                            <td><a href="{{ route('admin.maintenance.show', $log->vehicle) }}" class="btn btn-ghost btn-sm">History</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--muted)">No maintenance records yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())<div style="padding:12px 22px;border-top:1px solid var(--line)">{{ $logs->links() }}</div>@endif
        </div>

        {{-- Vehicles Due for PM --}}
        @if($upcomingPM->count() > 0)
        <div class="card">
            <div class="card-header"><span class="card-title">⚠ Due for Service</span></div>
            <div class="card-body" style="padding:0">
                @foreach($upcomingPM as $v)
                @php $lastLog = $v->maintenanceLogs->first(); @endphp
                <div style="padding:14px 22px;{{ !$loop->last ? 'border-bottom:1px solid var(--line)' : '' }}">
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <div>
                            <div style="font-weight:700">{{ $v->name }}</div>
                            <div style="font-size:.78rem;color:var(--muted)">{{ $v->plate_number }} · {{ number_format($v->odometer) }} km current</div>
                        </div>
                        <a href="{{ route('admin.maintenance.show', $v) }}" class="btn btn-ghost btn-sm">View</a>
                    </div>
                    @if($lastLog)
                    <div style="margin-top:8px;font-size:.8rem;color:var(--orange-l)">
                        Last: {{ $lastLog->service_type }} on {{ $lastLog->service_date->format('M d, Y') }}
                        @if($lastLog->next_service_due_mileage)
                        · Next due: {{ number_format($lastLog->next_service_due_mileage) }} km
                        @endif
                        @if($lastLog->next_service_due_date)
                        · Due: {{ $lastLog->next_service_due_date->format('M d, Y') }}
                        @endif
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
