@extends('layouts.app')
@section('title','Booking #' . $booking->id)
@section('page-title','Booking #' . $booking->id)
@section('content')
<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:24px">
    <div>
        <div class="card" style="margin-bottom:16px">
            <div class="card-header">
                <span class="card-title">Booking Details</span>
                @php $map=['pending_payment'=>'by','awaiting_verification'=>'bb','confirmed'=>'bg_','rejected'=>'br','ongoing'=>'bo','completed'=>'bgy','cancelled'=>'br']; @endphp
                <span class="badge {{ $map[$booking->status]??'bgy' }}">{{ ucwords(str_replace('_',' ',$booking->status)) }}</span>
            </div>
            <div class="card-body">
                <div style="display:grid;gap:10px;font-size:.9rem">
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.06)"><span style="color:rgba(240,242,255,.5)">Customer</span><strong>{{ $booking->full_name }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.06)"><span style="color:rgba(240,242,255,.5)">Email</span><strong>{{ $booking->email }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.06)"><span style="color:rgba(240,242,255,.5)">Phone</span><strong>{{ $booking->phone }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.06)"><span style="color:rgba(240,242,255,.5)">License #</span><strong>{{ $booking->drivers_license_number }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.06)"><span style="color:rgba(240,242,255,.5)">Vehicle</span><strong>{{ $booking->vehicle?->name }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.06)"><span style="color:rgba(240,242,255,.5)">Pickup</span><strong>{{ $booking->pickup_date?->format('M d, Y') }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.06)"><span style="color:rgba(240,242,255,.5)">Return</span><strong>{{ $booking->return_date?->format('M d, Y') }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.06)"><span style="color:rgba(240,242,255,.5)">Duration</span><strong>{{ $booking->duration_in_days }} days</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0"><span style="color:rgba(240,242,255,.5)">Total</span><strong style="color:#ff8c3a;font-size:1.1rem">₱{{ number_format($booking->total_amount,0) }}</strong></div>
                </div>
            </div>
        </div>

        @if($booking->payment)
        <div class="card" style="margin-bottom:16px">
            <div class="card-header"><span class="card-title">Payment Proof</span>
                <span class="badge {{ $booking->payment->status==='verified'?'bg_':($booking->payment->status==='rejected'?'br':'by') }}">{{ ucfirst($booking->payment->status) }}</span>
            </div>
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;margin-bottom:12px;font-size:.9rem"><span style="color:rgba(240,242,255,.5)">Reference Code</span><strong>{{ $booking->payment->reference_code }}</strong></div>
                <img src="{{ $booking->payment->screenshot_url }}" style="width:100%;border-radius:10px;max-height:320px;object-fit:contain;background:#06091b">
            </div>
        </div>
        @endif

        @if($booking->admin_notes)
        <div class="card" style="margin-bottom:16px">
            <div class="card-header"><span class="card-title">Admin Notes</span></div>
            <div class="card-body"><p style="color:rgba(240,242,255,.7);font-size:.9rem">{{ $booking->admin_notes }}</p></div>
        </div>
        @endif
    </div>

    <div>
        @if(in_array($booking->status,['awaiting_verification','confirmed']))
        <div class="card" style="margin-bottom:16px">
            <div class="card-header"><span class="card-title">Actions</span></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:12px">
                @if($booking->status==='awaiting_verification')
                <form method="POST" action="{{ route('admin.bookings.approve',$booking) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Admin Notes (optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="2" placeholder="Add a note for the customer…"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success" style="width:100%">✓ Approve Booking</button>
                </form>
                <hr style="border-color:rgba(255,255,255,.06)">
                <form method="POST" action="{{ route('admin.bookings.reject',$booking) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Rejection Reason <span style="color:#f87171">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="2" placeholder="Explain why…" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger" style="width:100%">✕ Reject Booking</button>
                </form>
                @endif
                @if($booking->status==='confirmed')
                <form method="POST" action="{{ route('admin.bookings.ongoing',$booking) }}">@csrf
                    <button type="submit" class="btn btn-primary" style="width:100%">→ Mark as Ongoing</button>
                </form>
                @endif
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header"><span class="card-title">Vehicle</span></div>
            <div class="card-body">
                <img src="{{ $booking->vehicle?->image_url }}" style="width:100%;border-radius:10px;aspect-ratio:16/9;object-fit:cover;margin-bottom:14px">
                <div style="font-weight:700;margin-bottom:4px">{{ $booking->vehicle?->name }}</div>
                <div style="font-size:.85rem;color:rgba(240,242,255,.5)">{{ $booking->vehicle?->type }} · {{ $booking->vehicle?->transmission }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
