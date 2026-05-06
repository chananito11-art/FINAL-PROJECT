@extends('layouts.customer')
@section('title','Booking #' . $booking->id . ' Details')
@push('styles')
<style>
    .wrap{max-width:720px;margin:40px auto;padding:0 20px 80px}
    .wrap h1{font-size:1.5rem;font-weight:900;letter-spacing:-.04em;margin-bottom:4px}
    .wrap>.sub{color:var(--muted);margin-bottom:24px}
    .card{background:var(--card-bg);border:1px solid var(--line);border-radius:16px;padding:22px;margin-bottom:16px}
    .card h2{font-size:.95rem;font-weight:800;margin-bottom:16px;text-transform:uppercase;letter-spacing:.04em;color:var(--text-dim)}
    .row{display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--line);font-size:.9rem;color:var(--muted)}
    .row:last-child{border-bottom:none}
    .row strong{color:var(--text)}
    .badge{display:inline-flex;padding:5px 13px;border-radius:999px;font-size:.82rem;font-weight:700}
    .s-awaiting_approval{background:rgba(59,130,246,.15);color:#60a5fa}
    .s-pending_payment{background:rgba(245,158,11,.15);color:#fbbf24}
    .s-awaiting_verification{background:rgba(59,130,246,.15);color:#60a5fa}
    .s-confirmed{background:rgba(34,197,94,.15);color:#4ade80}
    .s-rejected{background:rgba(239,68,68,.15);color:#f87171}
    .s-ongoing{background:rgba(255,107,0,.15);color:#ff8c3a}
    .s-completed{background:var(--badge-y);color:var(--muted)}
    .s-cancelled{background:rgba(239,68,68,.12);color:#f87171}
    .timeline{display:flex;flex-direction:column;gap:0}
    .t-step{display:flex;gap:16px;position:relative}
    .t-step::before{content:'';position:absolute;left:11px;top:28px;bottom:-4px;width:2px;background:var(--line)}
    .t-step:last-child::before{display:none}
    .t-dot{width:24px;height:24px;border-radius:50%;border:2px solid var(--line);background:var(--dark2);display:grid;place-items:center;flex-shrink:0;margin-top:2px}
    .t-dot.done{border-color:#ff6b00;background:rgba(255,107,0,.2)}
    .t-dot.active{border-color:#ff8c3a;background:rgba(255,107,0,.3);box-shadow:0 0 8px rgba(255,107,0,.4)}
    .t-content{padding:0 0 20px}
    .t-label{font-size:.9rem;font-weight:700;margin-bottom:2px}
    .t-desc{font-size:.8rem;color:var(--text-dim)}
    .pay-btn{display:block;width:100%;height:48px;background:linear-gradient(135deg,#ff8c3a,#ff6b00);border:none;border-radius:12px;color:#fff;font-family:inherit;font-size:.95rem;font-weight:700;cursor:pointer;text-decoration:none;text-align:center;line-height:48px;box-shadow:0 4px 14px rgba(255,107,0,.3)}
    .rejection-box{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);border-radius:12px;padding:14px;color:#f87171;font-size:.88rem}
    .cancel-btn{display:block;width:100%;height:44px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);border-radius:12px;color:#f87171;font-family:inherit;font-size:.9rem;font-weight:600;cursor:pointer;margin-top:10px;transition:background .2s}
    .cancel-btn:hover{background:rgba(239,68,68,.2)}
    /* Modal */
    .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1000;place-items:center}
    .modal-overlay.open{display:grid}
    .modal-box{background:var(--dark2);border:1px solid var(--line);border-radius:20px;padding:28px;max-width:400px;width:90%}
    .modal-box h3{font-size:1.1rem;font-weight:800;margin-bottom:10px}
    .modal-box p{font-size:.9rem;color:var(--muted);margin-bottom:22px}
    .modal-actions{display:flex;gap:10px}
    .modal-actions button{flex:1;height:42px;border-radius:10px;font-family:inherit;font-size:.9rem;font-weight:600;cursor:pointer;border:none}
    .btn-modal-cancel{background:var(--hover-bg);color:var(--muted)}
    .btn-modal-confirm{background:rgba(239,68,68,.2);color:#f87171;border:1px solid rgba(239,68,68,.3)!important}
</style>
@endpush
@section('content')
<div class="wrap">
    <div style="margin-bottom:12px"><a href="{{ route('customer.tracking.index') }}" style="color:var(--muted);text-decoration:none;font-size:.88rem">← My Bookings</a></div>
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:24px">
        <h1>Booking #{{ $booking->id }}</h1>
        <span class="badge s-{{ $booking->status }}">{{ ucwords(str_replace('_',' ',$booking->status)) }}</span>
    </div>

    @if($booking->status === 'rejected' && $booking->rejection_reason)
    <div class="rejection-box" style="margin-bottom:16px">
        <strong>Rejection Reason:</strong> {{ $booking->rejection_reason }}
    </div>
    @endif

    @if($booking->status === 'cancelled')
    <div class="rejection-box" style="margin-bottom:16px">
        <strong>Cancellation Reason:</strong>
        @php
        $reasons = ['hold_expired'=>'Reservation hold expired','customer_cancelled'=>'Cancelled by you','admin_cancelled'=>'Cancelled by admin'];
        @endphp
        {{ $reasons[$booking->cancellation_reason] ?? $booking->cancellation_reason }}
        @if($booking->admin_notes) — {{ $booking->admin_notes }} @endif
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
        @if($booking->hold_expires_at && $booking->status === 'pending_payment')
        <div class="row"><span>Hold Expires</span><strong style="color:#fbbf24">{{ $booking->hold_expires_at->format('M d, Y h:i A') }}</strong></div>
        @endif
    </div>

    @if($booking->status === 'awaiting_approval')
    <div style="background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.2);border-radius:16px;padding:22px;margin-bottom:16px;text-align:center">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2" style="margin-bottom:12px"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <div style="font-weight:800;color:var(--text);margin-bottom:6px">Awaiting Admin Approval</div>
        <div style="font-size:.88rem;color:var(--muted)">Your booking request is being reviewed. Once approved, you will have 1 hour to complete the payment.</div>
    </div>
    @endif

    @if($booking->payment)
    <div class="card">
        <h2>Payment</h2>
        <div class="row"><span>Reference Code</span><strong>{{ $booking->payment->reference_code }}</strong></div>
        @if($booking->payment->gcash_transaction_reference_number)
        <div class="row"><span>GCash Transaction #</span><strong>{{ $booking->payment->gcash_transaction_reference_number }}</strong></div>
        @endif
        <div class="row"><span>Amount Submitted</span><strong>PHP {{ number_format($booking->payment->amount_submitted ?? $booking->payment->amount, 0) }}</strong></div>
        <div class="row"><span>Status</span><strong>{{ ucfirst($booking->payment->status) }}</strong></div>
        @if($booking->payment->screenshot_path)
        <div style="margin-top:14px">
            <p style="font-size:.8rem;color:var(--text-dim);margin-bottom:8px">PAYMENT SCREENSHOT</p>
            <img src="{{ $booking->payment->screenshot_url }}" style="width:100%;border-radius:10px;max-height:300px;object-fit:contain;background:var(--dark2)">
        </div>
        @endif
    </div>
    @elseif($booking->status === 'pending_payment')
    <a href="{{ route('customer.payment.show', $booking) }}" class="pay-btn" style="margin-bottom:10px">Complete GCash Payment →</a>

    {{-- Cancel button (only for pending_payment) --}}
    <button class="cancel-btn" id="cancelBtn" type="button">Cancel Reservation</button>
    @endif

    <div class="card">
        <h2>Status Timeline</h2>
        <div class="timeline">
            @php
            $steps=[
                ['awaiting_approval','Awaiting Approval','Admin is reviewing your request'],
                ['pending_payment','Pending Payment','Request approved! Payment required'],
                ['awaiting_verification','Awaiting Verification','Payment submitted, waiting for review'],
                ['confirmed','Confirmed','Booking confirmed! Prepare for pickup'],
                ['ongoing','Ongoing','Vehicle is currently with you'],
                ['completed','Completed','Rental completed. Thank you!'],
            ];
            $order=['awaiting_approval'=>0,'pending_payment'=>1,'awaiting_verification'=>2,'confirmed'=>3,'ongoing'=>4,'completed'=>5];
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

{{-- Cancel Modal --}}
<div class="modal-overlay" id="cancelModal">
    <div class="modal-box">
        <h3>Cancel Reservation?</h3>
        <p>This will permanently cancel Booking #{{ $booking->id }} for <strong>{{ $booking->vehicle->name }}</strong>. This action cannot be undone.</p>
        <div class="modal-actions">
            <button class="btn-modal-cancel" id="modalClose">Keep Booking</button>
            <form method="POST" action="{{ route('customer.bookings.cancel', $booking) }}" style="flex:1">
                @csrf
                <button type="submit" class="btn-modal-confirm" style="width:100%">Yes, Cancel</button>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
const btn = document.getElementById('cancelBtn');
const modal = document.getElementById('cancelModal');
const closeBtn = document.getElementById('modalClose');
if (btn) btn.addEventListener('click', () => modal.classList.add('open'));
if (closeBtn) closeBtn.addEventListener('click', () => modal.classList.remove('open'));
modal?.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('open'); });
</script>
@endpush
