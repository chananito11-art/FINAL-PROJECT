@extends('layouts.app')
@section('title','Booking #' . $booking->id)
@section('page-title','Booking #' . $booking->id)
@section('content')
<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:24px">
    <div>
        <div class="card" style="margin-bottom:16px">
            <div class="card-header">
                <span class="card-title">Booking Details</span>
                @php $map=['awaiting_approval'=>'bb','pending_payment'=>'by','awaiting_verification'=>'bb','confirmed'=>'bg_','rejected'=>'br','ongoing'=>'bo','completed'=>'bgy','cancelled'=>'br']; @endphp
                <span class="badge {{ $map[$booking->status]??'bgy' }}">{{ ucwords(str_replace('_',' ',$booking->status)) }}</span>
            </div>
            <div class="card-body">
                <div style="display:grid;gap:10px;font-size:.9rem">
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)"><span style="color:var(--text-dim)">Customer</span><strong>{{ $booking->full_name }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)"><span style="color:var(--text-dim)">Email</span><strong>{{ $booking->email }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)"><span style="color:var(--text-dim)">Phone</span><strong>{{ $booking->phone }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)"><span style="color:var(--text-dim)">License #</span><strong>{{ $booking->drivers_license_number }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)"><span style="color:var(--text-dim)">Vehicle</span><strong>{{ $booking->vehicle?->name }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)"><span style="color:var(--text-dim)">Pickup</span><strong>{{ $booking->pickup_date?->format('M d, Y') }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)"><span style="color:var(--text-dim)">Return</span><strong>{{ $booking->return_date?->format('M d, Y') }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)"><span style="color:var(--text-dim)">Duration</span><strong>{{ $booking->duration_in_days }} days</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0"><span style="color:var(--text-dim)">Total</span><strong style="color:#ff8c3a;font-size:1.1rem">₱{{ number_format($booking->total_amount,0) }}</strong></div>
                </div>
            </div>
        </div>

        @if($booking->payment)
        <div class="card" style="margin-bottom:16px">
            <div class="card-header"><span class="card-title">Payment Proof</span>
                <span class="badge {{ $booking->payment->status==='verified'?'bg_':($booking->payment->status==='rejected'?'br':'by') }}">{{ ucfirst($booking->payment->status) }}</span>
            </div>
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;margin-bottom:12px;font-size:.9rem"><span style="color:var(--text-dim)">Reference Code</span><strong>{{ $booking->payment->reference_code }}</strong></div>
                <img src="{{ $booking->payment->screenshot_url }}" style="width:100%;border-radius:10px;max-height:320px;object-fit:contain;background:#06091b">
            </div>
        </div>
        @endif

        @if($booking->admin_notes)
        <div class="card" style="margin-bottom:16px">
            <div class="card-header"><span class="card-title">Admin Notes</span></div>
            <div class="card-body"><p style="color:var(--muted);font-size:.9rem">{{ $booking->admin_notes }}</p></div>
        </div>
        @endif
    </div>

        @if(in_array($booking->status, ['awaiting_approval', 'awaiting_verification', 'confirmed']))
        <div class="card" style="margin-bottom:16px">
            <div class="card-header"><span class="card-title">Actions</span></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:12px">
                @if($booking->status === 'awaiting_approval')
                <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Admin Notes (optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="2" placeholder="Add a note for the customer…"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success" style="width:100%">✓ Approve Request</button>
                </form>
                <hr style="border-color:var(--line)">
                <form method="POST" action="{{ route('admin.bookings.reject', $booking) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Rejection Reason <span style="color:#f87171">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="2" placeholder="Explain why…" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger" style="width:100%">✕ Reject Request</button>
                </form>
                @endif

                @if($booking->status === 'awaiting_verification' && $booking->payment)
                <div style="padding:12px;background:rgba(255,107,0,.05);border-radius:10px;border:1px solid rgba(255,107,0,.2);margin-bottom:8px">
                    <div style="font-size:.85rem;color:var(--orange-l);font-weight:600;margin-bottom:4px">Payment Verification Required</div>
                    <div style="font-size:.8rem;color:var(--muted)">A payment proof has been uploaded. Please verify it in the Payments section.</div>
                </div>
                <a href="{{ route('admin.payments.show', $booking->payment) }}" class="btn btn-primary" style="width:100%">Verify Payment Now</a>
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
                <div style="font-size:.85rem;color:var(--text-dim)">{{ $booking->vehicle?->type }} · {{ $booking->vehicle?->transmission }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
