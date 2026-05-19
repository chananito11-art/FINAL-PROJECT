@extends('layouts.app')
@section('title', 'Dispatch Board')
@section('page-title', 'Vehicle Dispatch Calendar')

@push('styles')
<style>
    .dispatch-grid {
        display: grid;
        grid-template-columns: 200px 1fr;
        background: var(--dark);
        border: 1px solid var(--line);
        border-radius: 16px;
        overflow: hidden;
    }
    .vehicle-labels-column {
        background: var(--dark);
        border-right: 1px solid var(--line);
        z-index: 15;
    }
    .column-header {
        background: var(--ghost-bg);
        padding: 18px 20px;
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--muted);
        border-bottom: 1px solid var(--line);
        height: 63px;
    }
    .calendar-container {
        overflow-x: auto;
        position: relative;
    }
    .calendar-header {
        display: flex;
        background: var(--ghost-bg);
        border-bottom: 1px solid var(--line);
        height: 63px;
    }
    .day-cell {
        width: 80px;
        min-width: 80px;
        padding: 10px 0;
        text-align: center;
        border-right: 1px solid var(--line);
        font-size: .75rem;
    }
    .day-cell.today { background: rgba(255,107,0,.1); color: var(--orange-l); font-weight: 700; }
    
    .vehicle-row {
        display: flex;
        border-bottom: 1px solid var(--line);
        height: 60px;
        position: relative;
    }
    .vehicle-label {
        height: 60px;
        padding: 12px 20px;
        border-bottom: 1px solid var(--line);
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .booking-bar {
        position: absolute;
        height: 40px;
        top: 10px;
        border-radius: 8px;
        background: var(--orange);
        color: #fff;
        font-size: .75rem;
        padding: 4px 10px;
        display: flex;
        align-items: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        z-index: 2;
        box-shadow: 0 4px 8px rgba(0,0,0,.2);
        cursor: pointer;
        transition: transform .2s;
    }
    .booking-bar:hover { transform: scaleY(1.05); z-index: 100; overflow: visible; }
    
    .booking-bar.confirmed { background: var(--green); }
    .booking-bar.ongoing { background: var(--blue); }
    .booking-bar.pending_payment { background: var(--yellow); color: #000; }
</style>
@endpush

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <div style="display:flex;gap:12px">
        <a href="{{ route('admin.dispatch.index', ['start' => $start->copy()->subDays(14)->toDateString()]) }}" class="btn btn-ghost btn-sm">← Prev 2 Weeks</a>
        <a href="{{ route('admin.dispatch.index', ['start' => now()->startOfWeek()->toDateString()]) }}" class="btn btn-ghost btn-sm">Today</a>
        <a href="{{ route('admin.dispatch.index', ['start' => $start->copy()->addDays(14)->toDateString()]) }}" class="btn btn-ghost btn-sm">Next 2 Weeks →</a>
    </div>
    <div style="font-weight:600;color:var(--muted)">{{ $start->format('M d') }} — {{ $end->format('M d, Y') }}</div>
</div>

<div class="dispatch-grid">
    {{-- Left Column: Vehicle Labels --}}
    <div class="vehicle-labels-column">
        <div class="column-header">Vehicles</div>
        @foreach($vehicles as $vehicle)
        <div class="vehicle-label">
            <div style="font-weight:700;font-size:.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $vehicle->name }}">
                {{ $vehicle->name }}
            </div>
            <div style="font-size:.7rem;color:var(--muted);letter-spacing:0.02em">
                {{ $vehicle->plate_number }}
            </div>
        </div>
        @endforeach
    </div>

    {{-- Right Column: Scrollable Calendar --}}
    <div class="calendar-container">
        <div class="calendar-header">
            @foreach($days as $day)
            <div class="day-cell {{ $day->isToday() ? 'today' : '' }}">
                <div style="opacity:.6">{{ $day->format('D') }}</div>
                <div style="font-size:1rem">{{ $day->format('d') }}</div>
            </div>
            @endforeach
        </div>
        
        <div class="calendar-rows">
            @foreach($vehicles as $vehicle)
            <div class="vehicle-row">
                {{-- Grid Background --}}
                @foreach($days as $day)
                <div class="day-cell" style="background:transparent"></div>
                @endforeach

                {{-- Booking Bars --}}
                @foreach($vehicle->bookings as $booking)
                    @php
                        $bStart = $booking->pickup_date;
                        $bEnd = $booking->return_date;
                        
                        // Clip to calendar range
                        $renderStart = $bStart->lt($start) ? $start : $bStart;
                        $renderEnd = $bEnd->gt($end) ? $end : $bEnd;
                        
                        $offsetDays = $renderStart->diffInDays($start, false) * -1;
                        $durationDays = $renderStart->diffInDays($renderEnd) + 1;
                        
                        $left = $offsetDays * 80;
                        $width = $durationDays * 80 - 4;
                    @endphp
                    <div class="booking-bar {{ $booking->status }}" 
                         style="left: {{ $left }}px; width: {{ $width }}px;"
                         onclick="window.location='{{ route('admin.bookings.show', $booking) }}'"
                         title="{{ $booking->first_name }} - {{ $booking->status }}">
                        #{{ $booking->id }} {{ $booking->first_name }}
                    </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Sidebar Overlay for Vehicle labels --}}
<style>
    .vehicle-list {
        position: absolute;
        left: 0;
        top: 40px; /* match header height */
        width: 200px;
        z-index: 20;
    }
</style>
@endsection
