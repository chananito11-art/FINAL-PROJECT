@extends('layouts.app')
@section('title','Payment #' . $payment->id)
@section('page-title','Payment Detail')
@push('styles')
<style>
.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start}
.info-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--line);font-size:.9rem;color:var(--muted)}
.info-row:last-child{border-bottom:none}
.info-row strong{color:var(--text)}
.gcash-ref{font-size:1.4rem;font-weight:900;font-family:monospace;color:#60a5fa;letter-spacing:.05em;background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.2);border-radius:10px;padding:14px 20px;text-align:center;margin:12px 0}
.mismatch{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);border-radius:10px;padding:12px;color:#f87171;font-size:.88rem;margin:8px 0}
.match{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);border-radius:10px;padding:12px;color:#4ade80;font-size:.88rem;margin:8px 0}
.zoom-img{cursor:zoom-in;transition:opacity .2s}
.zoom-img:hover{opacity:.85}
/* Modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:1000;place-items:center}
.modal-overlay.open{display:grid}
.modal-box{background:var(--dark2);border:1px solid var(--line);border-radius:20px;padding:28px;max-width:480px;width:94%}
.modal-box h3{font-size:1.1rem;font-weight:800;margin-bottom:12px}
.modal-actions{display:flex;gap:10px;margin-top:18px}
.modal-actions button{flex:1;height:42px;border-radius:10px;font-family:inherit;font-size:.9rem;font-weight:600;cursor:pointer;border:none}
/* Image lightbox */
.lightbox{display:none;position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:2000;place-items:center;cursor:zoom-out}
.lightbox.open{display:grid}
.lightbox img{max-width:92vw;max-height:90vh;border-radius:12px}
</style>
@endpush
@section('content')

<div style="margin-bottom:16px;display:flex;gap:10px;align-items:center">
    <a href="{{ route('admin.payments.index') }}" class="btn btn-ghost btn-sm">← Back to Payments</a>
    @if($payment->status==='pending') <span class="badge by">Pending Verification</span>
    @elseif($payment->status==='verified') <span class="badge bg_">Verified</span>
    @else <span class="badge br">Rejected</span> @endif
</div>

<div class="detail-grid">
    {{-- Left column --}}
    <div>
        {{-- Booking Summary --}}
        <div class="card" style="margin-bottom:16px">
            <div class="card-header"><span class="card-title">Booking Summary</span></div>
            <div class="card-body">
                <div class="info-row"><span>Booking #</span><strong>#{{ $payment->booking?->id }}</strong></div>
                <div class="info-row"><span>Vehicle</span><strong>{{ $payment->booking?->vehicle?->name }}</strong></div>
                <div class="info-row"><span>Pickup</span><strong>{{ $payment->booking?->pickup_date?->format('M d, Y') }}</strong></div>
                <div class="info-row"><span>Return</span><strong>{{ $payment->booking?->return_date?->format('M d, Y') }}</strong></div>
                <div class="info-row"><span>Duration</span><strong>{{ $payment->booking?->duration_in_days }} day(s)</strong></div>
                <div class="info-row" style="margin-top:8px;padding-top:8px;border-top:1px dashed var(--line)"><span>Total Cost</span><strong>₱{{ number_format($payment->booking?->total_amount,0) }}</strong></div>
                <div class="info-row"><span>Already Paid</span><strong style="color:var(--green)">₱{{ number_format($payment->booking?->paid_amount,0) }}</strong></div>
                <div class="info-row"><span>Remaining Balance</span><strong style="color:var(--orange-l);font-size:1.05rem">₱{{ number_format($payment->booking?->balance_amount,0) }}</strong></div>
            </div>
        </div>

        {{-- Customer Info --}}
        <div class="card" style="margin-bottom:16px">
            <div class="card-header"><span class="card-title">Customer</span></div>
            <div class="card-body">
                <div class="info-row"><span>Name</span><strong>{{ $payment->booking?->user?->first_name }} {{ $payment->booking?->user?->last_name }}</strong></div>
                <div class="info-row"><span>Email</span><strong>{{ $payment->booking?->user?->email }}</strong></div>
                <div class="info-row"><span>Phone</span><strong>{{ $payment->booking?->user?->phone ?? '—' }}</strong></div>
                <div class="info-row"><span>Driver's License</span><strong>{{ $payment->booking?->drivers_license_number }}</strong></div>
            </div>
        </div>

        {{-- GCash Details --}}
        <div class="card">
            <div class="card-header"><span class="card-title">GCash Payment Details</span></div>
            <div class="card-body">
                <p style="font-size:.78rem;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">GCash Transaction Reference Number</p>
                <div class="gcash-ref">{{ $payment->gcash_transaction_reference_number ?? '—' }}</div>

                <div class="info-row"><span>Remaining Balance</span><strong style="color:var(--orange-l)">₱{{ number_format($payment->booking?->balance_amount,0) }}</strong></div>
                <div class="info-row"><span>Amount Submitted</span>
                    <strong>₱{{ number_format($payment->amount_submitted ?? 0,0) }}</strong>
                </div>

                @php 
                    $submitted = $payment->amount_submitted ?? 0;
                    $balance = $payment->booking?->balance_amount ?? 0;
                    $isFull = $submitted >= $balance;
                @endphp

                @if($submitted > 0 && $isFull)
                <div class="match">✓ Payment covers the full remaining balance.</div>
                @elseif($submitted > 0)
                <div class="mismatch" style="background:rgba(245,158,11,.1);border-color:rgba(245,158,11,.25);color:#fbbf24">
                    ⚠️ Partial Payment: Customer submitted ₱{{ number_format($submitted,0) }} towards a balance of ₱{{ number_format($balance,0) }}.
                </div>
                @endif

                @if($payment->amount_matched !== null)
                <div class="info-row"><span>Admin Verified Amount</span>
                    <strong>{{ $payment->amount_matched ? '✓ Matched' : '✕ Not Matched' }}</strong>
                </div>
                @endif
                <div class="info-row"><span>Ref Code</span><strong>{{ $payment->reference_code }}</strong></div>
                @if($payment->gcash_account_name)
                <div class="info-row"><span>GCash Account Name</span><strong>{{ $payment->gcash_account_name }}</strong></div>
                @endif
                @if($payment->admin_payment_notes)
                <div style="margin-top:10px;padding:10px;background:var(--hover-bg);border-radius:8px;font-size:.85rem;color:var(--muted)">
                    <strong style="color:var(--text)">Admin Notes:</strong> {{ $payment->admin_payment_notes }}
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right column --}}
    <div>
        {{-- Screenshot --}}
        <div class="card" style="margin-bottom:16px">
            <div class="card-header"><span class="card-title">Payment Screenshot</span></div>
            <div class="card-body">
                @if($payment->screenshot_path)
                <img src="{{ $payment->screenshot_url }}" alt="Payment Receipt"
                     class="zoom-img" id="receiptImg"
                     style="width:100%;border-radius:10px;max-height:360px;object-fit:contain;background:#06091b">
                <p style="font-size:.75rem;color:var(--muted);text-align:center;margin-top:8px">Click to zoom</p>
                @else
                <p style="color:var(--muted);font-size:.9rem">No screenshot uploaded.</p>
                @endif
            </div>
        </div>

        {{-- Verify / Reject Actions --}}
        @if($payment->status === 'pending')
        <div class="card" style="margin-bottom:16px">
            <div class="card-header"><span class="card-title">Verification Actions</span></div>
            <div class="card-body">
                <div style="display:flex;gap:10px">
                    <button class="btn btn-success" style="flex:1" id="verifyBtn">✓ Verify Payment</button>
                    <button class="btn btn-danger"  style="flex:1" id="rejectBtn">✕ Reject Payment</button>
                </div>
            </div>
        </div>
        @endif

        {{-- Refund Section --}}
        @if($payment->status === 'verified' && in_array($payment->booking?->status, ['cancelled', 'rejected']))
        <div class="card" style="margin-bottom:16px;border-color:rgba(245,158,11,.2)">
            <div class="card-header"><span class="card-title">Refund</span></div>
            <div class="card-body">
                @if($payment->refund_issued)
                <div class="match" style="margin-bottom:12px">✓ Refund issued on {{ $payment->refund_issued_at?->format('M d, Y') }}</div>
                <div class="info-row"><span>Refund GCash Ref</span><strong>{{ $payment->refund_gcash_reference }}</strong></div>
                @if($payment->refund_notes)<p style="font-size:.85rem;color:var(--muted);margin-top:8px">{{ $payment->refund_notes }}</p>@endif
                @else
                <p style="font-size:.85rem;color:var(--muted);margin-bottom:14px">This booking was cancelled after payment was verified. Record a manual GCash refund below.</p>
                <form method="POST" action="{{ route('admin.payments.refund', $payment) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">GCash Refund Reference Number</label>
                        <input type="text" name="refund_gcash_reference" class="form-control" placeholder="e.g. REF-1234567890" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Refund Notes (optional)</label>
                        <textarea name="refund_notes" class="form-control" rows="2" placeholder="Any additional notes…"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%">Record Refund</button>
                </form>
                @endif
            </div>
        </div>
        @endif

        @if($payment->verifiedBy)
        <div class="card">
            <div class="card-body">
                <div class="info-row"><span>{{ $payment->status === 'verified' ? 'Verified' : 'Reviewed' }} By</span><strong>{{ $payment->verifiedBy?->first_name }} {{ $payment->verifiedBy?->last_name }}</strong></div>
                <div class="info-row"><span>At</span><strong>{{ $payment->verified_at?->format('M d, Y h:i A') }}</strong></div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Verify Modal --}}
<div class="modal-overlay" id="verifyModal">
    <div class="modal-box">
        <h3>Verify Payment</h3>
        @php
            $submittedAmt  = $payment->amount_submitted ?? 0;
            $balanceAmt    = $payment->booking?->balance_amount ?? 0;
            $isFullPayment = $submittedAmt >= $balanceAmt;
        @endphp
        <div style="background:var(--hover-bg);border-radius:10px;padding:12px 14px;margin-bottom:14px;font-size:.88rem">
            @if($isFullPayment)
            <div style="color:#4ade80;font-weight:700;margin-bottom:4px">✓ Full Payment — clears the balance</div>
            <div style="color:var(--muted)">Submitted: ₱{{ number_format($submittedAmt,0) }} · Balance was: ₱{{ number_format($balanceAmt,0) }}</div>
            @else
            <div style="color:#fbbf24;font-weight:700;margin-bottom:4px">⚡ Partial Payment — balance will remain</div>
            <div style="color:var(--muted)">Submitted: ₱{{ number_format($submittedAmt,0) }} · Remaining after: ₱{{ number_format(max(0,$balanceAmt-$submittedAmt),0) }}</div>
            @endif
        </div>
        <form method="POST" action="{{ route('admin.payments.verify', $payment) }}">
            @csrf
            <input type="hidden" name="amount_matched" value="{{ $isFullPayment ? '1' : '0' }}">
            <div class="form-group">
                <label class="form-label">Admin Notes (optional)</label>
                <textarea name="admin_notes" class="form-control" rows="3" placeholder="Internal notes visible to admins only…"></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" id="closeVerify">Cancel</button>
                <button type="submit" class="btn btn-success" style="flex:1">Confirm Verify</button>
            </div>
        </form>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal-overlay" id="rejectModal">
    <div class="modal-box">
        <h3>Reject Payment</h3>
        <form method="POST" action="{{ route('admin.payments.reject', $payment) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Rejection Reason <span style="color:var(--red)">*</span></label>
                <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Explain why the payment is rejected…" required></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" id="closeReject">Cancel</button>
                <button type="submit" class="btn btn-danger" style="flex:1">Confirm Reject</button>
            </div>
        </form>
    </div>
</div>

{{-- Image Lightbox --}}
<div class="lightbox" id="lightbox">
    <img id="lightboxImg" src="" alt="Receipt">
</div>
@endsection
@push('scripts')
<script>
// Modals
const verifyBtn   = document.getElementById('verifyBtn');
const rejectBtn   = document.getElementById('rejectBtn');
const verifyModal = document.getElementById('verifyModal');
const rejectModal = document.getElementById('rejectModal');
if(verifyBtn) verifyBtn.addEventListener('click', () => verifyModal.classList.add('open'));
if(rejectBtn) rejectBtn.addEventListener('click', () => rejectModal.classList.add('open'));
document.getElementById('closeVerify')?.addEventListener('click', () => verifyModal.classList.remove('open'));
document.getElementById('closeReject')?.addEventListener('click', () => rejectModal.classList.remove('open'));

// Lightbox
const img = document.getElementById('receiptImg');
const lb  = document.getElementById('lightbox');
const lbImg = document.getElementById('lightboxImg');
if(img) img.addEventListener('click', () => { lbImg.src = img.src; lb.classList.add('open'); });
lb?.addEventListener('click', () => lb.classList.remove('open'));
</script>
@endpush
