@extends('layouts.app')
@section('title', 'Maintenance History — ' . $vehicle->name)
@section('page-title', $vehicle->name)

@section('content')
<div style="margin-bottom:24px">
    <a href="{{ route('admin.maintenance.index') }}" style="color:var(--muted);text-decoration:none;font-size:.9rem">← Back to Maintenance Tracking</a>
</div>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:24px">
    <div>
        <div class="card">
            <div class="card-header"><span class="card-title">Vehicle Info</span></div>
            <div class="card-body">
                <img src="{{ $vehicle->image_url }}" style="width:100%;border-radius:12px;margin-bottom:16px;aspect-ratio:16/9;object-fit:cover">
                <div style="display:grid;gap:12px">
                    <div style="display:flex;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid var(--line)"><span style="color:var(--muted)">Plate Number</span><strong>{{ $vehicle->plate_number }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid var(--line)"><span style="color:var(--muted)">Current Odometer</span><strong>{{ number_format($vehicle->odometer) }} km</strong></div>
                    <div style="display:flex;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid var(--line)"><span style="color:var(--muted)">Status</span><strong>{{ ucfirst($vehicle->status) }}</strong></div>
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--muted)">Total Logs</span><strong>{{ $vehicle->maintenanceLogs->count() }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header"><span class="card-title">Full Service History</span></div>
            <div class="tw">
                <table>
                    <thead><tr><th>Date</th><th>Type</th><th>Mileage</th><th>Cost</th><th>Performed By</th></tr></thead>
                    <tbody>
                        @forelse($vehicle->maintenanceLogs as $log)
                        <tr style="border-bottom:1px solid var(--line)">
                            <td style="padding:16px 22px">
                                <div style="font-weight:700">{{ $log->service_date->format('M d, Y') }}</div>
                                @if($log->next_service_due_mileage || $log->next_service_due_date)
                                <div style="font-size:.7rem;color:var(--orange-l);margin-top:4px">
                                    Next: {{ $log->next_service_due_mileage ? number_format($log->next_service_due_mileage).'km' : '' }} 
                                    {{ $log->next_service_due_date ? ' / '.$log->next_service_due_date->format('M d, Y') : '' }}
                                </div>
                                @endif
                            </td>
                            <td style="padding:16px 22px">
                                <div style="font-weight:600">{{ $log->service_type }}</div>
                                <div style="font-size:.8rem;color:var(--muted)">{{ $log->description }}</div>
                            </td>
                            <td style="padding:16px 22px">{{ number_format($log->mileage_at_service) }} km</td>
                            <td style="padding:16px 22px;font-weight:600">₱{{ number_format($log->cost, 2) }}</td>
                            <td style="padding:16px 22px;font-size:.85rem;color:var(--muted)">{{ $log->performed_by ?: '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center;padding:48px;color:var(--muted)">No history recorded for this vehicle.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
