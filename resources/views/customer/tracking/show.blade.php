@extends('layouts.customer')
@section('title','Booking #' . $booking->id . ' Details')
@push('styles')
<style>
    .wrap{max-width:720px;margin:40px auto;padding:0 20px 80px}
    .wrap h1{font-size:1.5rem;font-weight:900;letter-spacing:-.04em;margin-bottom:4px}
    .wrap>.sub{color:rgba(240,242,255,.55);margin-bottom:24px}
    .card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:22px;margin-bottom:16px}
    .card h2{font-size:.95rem;font-weight:800;margin-bottom:16px;text-transform:uppercase;letter-spacing:.04em;color:rgba(240,242,255,.6)}
    .row{display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid rgba(255,255,255,.05);font-size:.9rem;color:rgba(240,242,255,.65)}
    .row:last-child{border-bottom:none}
    .row strong{color:#f0f2ff}
    .badge{display:inline-flex;padding:5px 13px;border-radius:999px;font-size:.82rem;font-weight:700}
    .s-pending_payment{background:rgba(245,158,11,.15);color:#fbbf24}
    .s-awaiting_verification{background:rgba(59,130,246,.15);color:#60a5fa}
    .s-confirmed{background:rgba(34,197,94,.15);color:#4ade80}
    .s-rejected{background:rgba(239,68,68,.15);color:#f87171}
    .s-ongoing{background:rgba(255,107,0,.15);color:#ff8c3a}
    .s-completed{background:rgba(255,255,255,.08);color:rgba(240,242,255,.5)}
    .s-cancelled{background:rgba(239,68,68,.12);color:#f87171}
    .timeline{display:flex;flex-direction:column;gap:0}
    .t-step{display:flex;gap:16px;position:relative}
    .t-step::before{content:'';position:absolute;left:11px;top:28px;bottom:-4px;width:2px;background:rgba(255,255,255,.08)}
    .t-step:last-child::before{display:none}
    .t-dot{width:24px;height:24px;border-radius:50%;border:2px solid rgba(255,255,255,.15);background:#0d1128;display:grid;place-items:center;flex-shrink:0;margin-top:2px}
    .t-dot.done{border-color:#ff6b00;background:rgba(255,107,0,.2)}
    .t-dot.active{border-color:#ff8c3a;background:rgba(255,107,0,.3);box-shadow:0 0 8px rgba(255,107,0,.4)}
    .t-content{padding:0 0 20px}
    .t-label{font-size:.9rem;font-weight:700;margin-bottom:2px}
    .t-desc{font-size:.8rem;color:rgba(240,242,255,.45)}
    .pay-btn{display:block;width:100%;height:48px;background:linear-gradient(135deg,#ff8c3a,#ff6b00);border:none;border-radius:12px;color:#fff;font-family:inherit;font-size:.95rem;font-weight:700;cursor:pointer;text-decoration:none;text-align:center;line-height:48px;box-shadow:0 4px 14px rgba(255,107,0,.3)}
    .rejection-box{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);border-radius:12px;padding:14px;color:#f87171;font-size:.88rem}
</style>
@endpush
@section('content')
<div class="wrap">
    <div style="margin-bottom:12px"><a href="{{ route('customer.tracking.index') }}" style="color:rgba(240,242,255,.5);text-decoration:none;font-size:.88rem">← My Bookings</a></div>
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:24px">
        <h1>Booking #{{ $booking->id }}</h1>
        <span class="badge s-{{ $booking->status }}">{{ ucwords(str_replace('_',' ',$booking->status)) }}</span>
    </div>

    @if($booking->status === 'rejected' && $booking->rejection_reason)
    <div class="rejection-box" style="margin-bottom:16px">
        <strong>Rejection Reason:</strong> {{ $booking->rejection_reason }}
    </div>
    @endif

    <div class="card">
        <h2>Booking Details</h2>
        <div class="row"><span>Vehicle</span><strong>{{ $booking->vehicle->name }}</strong></div>
        <div class="row"><span>Pickup Date</span><strong>{{ $booking->pickup_date->format('M d, Y') }}</strong></div>
        <div class="row"><span>Return Date</span><strong>{{ $booking->return_date->format('M d, Y') }}</strong></div>
        <div class="row"><span>Duration</span><strong>{{ $booking->duration_in_days }} day(s)</strong></div>
        <div class="row"><span>Total Amount</span><strong style="color:#ff8c3a">PHP {{ number_format($booking->total_amount,0) }}</strong></div>
        <div class="row"><span>Driver's License</span><strong>{{ $booking->drivers_license_number }}</strong></div>
    </div>

    @if($booking->payment)
    <div class="card">
        <h2>Payment</h2>
        <div class="row"><span>Reference Code</span><strong>{{ $booking->payment->reference_code }}</strong></div>
        <div class="row"><span>Amount</span><strong>PHP {{ number_format($booking->payment->amount,0) }}</strong></div>
        <div class="row"><span>Status</span><strong>{{ ucfirst($booking->payment->status) }}</strong></div>
        @if($booking->payment->screenshot_path)
        <div style="margin-top:14px">
            <p style="font-size:.8rem;color:rgba(240,242,255,.5);margin-bottom:8px">PAYMENT SCREENSHOT</p>
            <img src="{{ $booking->payment->screenshot_url }}" style="width:100%;border-radius:10px;max-height:300px;object-fit:contain;background:#06091b">
        </div>
        @endif
    </div>
    @elseif($booking->status === 'pending_payment')
    <a href="{{ route('customer.payment.show', $booking) }}" class="pay-btn" style="margin-bottom:16px">Complete GCash Payment →</a>
    @endif

    <div class="card">
        <h2>Status Timeline</h2>
        <div class="timeline">
            @php
            $steps=[
                ['pending_payment','Pending Payment','Booking created, payment required'],
                ['awaiting_verification','Awaiting Verification','Payment submitted, waiting for admin review'],
                ['confirmed','Confirmed','Booking confirmed! Prepare for pickup'],
                ['ongoing','Ongoing','Vehicle is currently with you'],
                ['completed','Completed','Rental completed. Thank you!'],
            ];
            $order=['pending_payment'=>0,'awaiting_verification'=>1,'confirmed'=>2,'ongoing'=>3,'completed'=>4];
            $cur=$order[$booking->status]??-1;
            @endphp
            @foreach($steps as [$s,$label,$desc])
            @php $idx=$order[$s]??99; @endphp
            <div class="t-step">
                <div class="t-dot {{ $idx<$cur?'done':($idx===$cur?'active':'') }}">
                    @if($idx<=$cur)<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>@endif
                </div>
                <div class="t-content">
                    <div class="t-label" style="{{ $idx===$cur?'color:#ff8c3a':($idx<$cur?'color:#4ade80':'') }}">{{ $label }}</div>
                    <div class="t-desc">{{ $desc }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
