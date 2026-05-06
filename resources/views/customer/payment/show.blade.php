@extends('layouts.customer')
@section('title','GCash Payment — Booking #' . $booking->id)
@push('styles')
<style>
    .pay-wrap{max-width:680px;margin:48px auto;padding:0 20px 80px}
    .pay-wrap h1{font-size:1.6rem;font-weight:900;letter-spacing:-.04em;margin-bottom:4px}
    .pay-wrap>.sub{color:var(--muted);margin-bottom:28px}
    .info-card{background:var(--card-bg);border:1px solid var(--line);border-radius:16px;padding:22px;margin-bottom:20px}
    .info-card h2{font-size:1rem;font-weight:800;margin-bottom:16px;color:var(--text)}
    .gcash-box{background:linear-gradient(135deg,rgba(0,103,255,.15),rgba(0,103,255,.06));border:1px solid rgba(0,103,255,.25);border-radius:14px;padding:24px;text-align:center;margin-bottom:20px}
    .gcash-logo{font-size:1.4rem;font-weight:900;color:#4d8eff;letter-spacing:-.04em;margin-bottom:8px}
    .gcash-num{font-size:2rem;font-weight:900;letter-spacing:.08em;color:var(--text);margin-bottom:4px}
    .gcash-name{font-size:.9rem;color:var(--muted)}
    .amount-chip{display:inline-block;background:rgba(255,107,0,.15);border:1px solid rgba(255,107,0,.25);color:#ff8c3a;font-size:1.2rem;font-weight:900;padding:10px 24px;border-radius:12px;margin:16px 0}
    .detail-row{display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--line);font-size:.9rem;color:var(--muted)}
    .detail-row:last-child{border-bottom:none}
    .detail-row strong{color:var(--text)}
    .form-group{margin-bottom:16px}
    .form-label{display:block;font-size:.8rem;font-weight:600;color:var(--text-dim);margin-bottom:6px;text-transform:uppercase;letter-spacing:.04em}
    .form-label span.req{color:#ff8c3a}
    .form-control{width:100%;height:44px;background:var(--input-bg);border:1px solid var(--line);border-radius:10px;color:var(--text);font-family:inherit;font-size:.92rem;padding:0 13px;outline:none;transition:border-color .2s}
    .form-control:focus{border-color:var(--orange);box-shadow:0 0 0 3px var(--og)}
    .upload-zone{border:2px dashed var(--line);border-radius:12px;padding:28px;text-align:center;color:var(--muted);cursor:pointer;transition:border-color .2s}
    .upload-zone:hover{border-color:var(--orange)}
    .upload-zone input{display:none}
    .submit-btn{width:100%;height:50px;background:linear-gradient(135deg,#ff8c3a,#ff6b00);border:none;border-radius:12px;color:#fff;font-family:inherit;font-size:1rem;font-weight:700;cursor:pointer;box-shadow:0 6px 20px rgba(255,107,0,.3);transition:filter .2s,transform .2s;margin-top:8px}
    .submit-btn:hover{filter:brightness(1.08);transform:translateY(-2px)}
    #preview{display:none;width:100%;border-radius:10px;margin-top:12px;max-height:240px;object-fit:contain}
    /* Hold timer */
    .hold-timer{display:flex;align-items:center;gap:12px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);border-radius:12px;padding:14px 18px;margin-bottom:20px}
    .hold-timer svg{flex-shrink:0;color:#f59e0b}
    .hold-label{font-size:.82rem;color:var(--muted)}
    .hold-countdown{font-size:1.1rem;font-weight:800;color:#fbbf24;font-variant-numeric:tabular-nums}
    .hold-expired{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.25)}
    .hold-expired .hold-countdown{color:#f87171}
</style>
@endpush
@section('content')
<div class="pay-wrap">
    <h1>Complete Your Payment</h1>
    <p class="sub">Booking #{{ $booking->id }} · {{ $booking->vehicle->name }}</p>

    @if($booking->hold_expires_at)
    <div class="hold-timer" id="holdTimerBox" data-expires="{{ $booking->hold_expires_at->toIso8601String() }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <div>
            <div class="hold-label">Reservation hold expires in</div>
            <div class="hold-countdown" id="holdCountdown">calculating…</div>
        </div>
    </div>
    @endif

    <div class="gcash-box">
        <div class="gcash-logo">GCash</div>
        <div class="gcash-num">0917 123 4567</div>
        <div class="gcash-name">OrangeCrush Car Rentals</div>
        <div class="amount-chip">PHP {{ number_format($booking->total_amount, 0) }}</div>
        <p style="font-size:.83rem;color:var(--text-dim)">Send the exact amount above, then fill the form below.</p>
    </div>

    <div class="info-card">
        <h2>Booking Summary</h2>
        <div class="detail-row"><span>Vehicle</span><strong>{{ $booking->vehicle->name }}</strong></div>
        <div class="detail-row"><span>Pickup</span><strong>{{ $booking->pickup_date->format('M d, Y') }}</strong></div>
        <div class="detail-row"><span>Return</span><strong>{{ $booking->return_date->format('M d, Y') }}</strong></div>
        <div class="detail-row"><span>Duration</span><strong>{{ $booking->duration_in_days }} day(s)</strong></div>
        <div class="detail-row"><span>Total</span><strong style="color:#ff8c3a">PHP {{ number_format($booking->total_amount, 0) }}</strong></div>
    </div>

    <div class="info-card">
        <h2>Upload Proof of Payment</h2>
        <form method="POST" action="{{ route('customer.payment.store', $booking) }}" enctype="multipart/form-data" id="payForm">
            @csrf
            @if($errors->any())
                <div style="background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);border-radius:10px;padding:11px;color:#f87171;font-size:.88rem;margin-bottom:14px">{{ $errors->first() }}</div>
            @endif

            <div class="form-group">
                <label class="form-label">GCash Reference Code <span class="req">*</span></label>
                <input type="text" name="reference_code" class="form-control" placeholder="e.g. GC-2024-001234" value="{{ old('reference_code') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">GCash Transaction Reference Number <span class="req">*</span>
                    <span style="font-size:.72rem;color:var(--text-dim);text-transform:none;letter-spacing:0">(found on your GCash receipt)</span>
                </label>
                <input type="text" name="gcash_transaction_reference_number" class="form-control" placeholder="e.g. 1234 5678 9012" value="{{ old('gcash_transaction_reference_number') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Amount You Sent (₱) <span class="req">*</span></label>
                <input type="number" step="0.01" name="amount_submitted" class="form-control" placeholder="{{ $booking->total_amount }}" value="{{ old('amount_submitted') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Your GCash Account Name <span style="font-size:.72rem;color:var(--text-dim);text-transform:none">(optional)</span></label>
                <input type="text" name="gcash_account_name" class="form-control" placeholder="e.g. Juan Dela Cruz" value="{{ old('gcash_account_name') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Screenshot / Receipt <span class="req">*</span></label>
                <label class="upload-zone" id="uploadZone">
                    <input type="file" name="screenshot" id="screenshotInput" accept="image/*" required>
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <p style="margin-top:10px;font-size:.88rem" id="uploadLabel">Click to upload or drag &amp; drop<br><span style="font-size:.78rem">PNG, JPG up to 5 MB</span></p>
                </label>
                <img id="preview" src="" alt="Preview">
            </div>
            <button type="submit" class="submit-btn" id="payBtn">Submit Payment →</button>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
// Screenshot preview
document.getElementById('screenshotInput').addEventListener('change', function() {
    const f = this.files[0]; if (!f) return;
    document.getElementById('uploadLabel').textContent = f.name;
    const p = document.getElementById('preview');
    p.src = URL.createObjectURL(f); p.style.display = 'block';
});
document.getElementById('payForm').addEventListener('submit', function() {
    var b = document.getElementById('payBtn'); b.disabled = true; b.textContent = 'Submitting…';
});

// Hold countdown timer
(function() {
    const box = document.getElementById('holdTimerBox');
    const el  = document.getElementById('holdCountdown');
    if (!box || !el) return;

    const expires = new Date(box.dataset.expires).getTime();

    function tick() {
        const remaining = expires - Date.now();
        if (remaining <= 0) {
            el.textContent = 'EXPIRED';
            box.classList.add('hold-expired');
            setTimeout(() => {
                window.location.href = '{{ route("customer.tracking.index") }}?expired=1';
            }, 3000);
            return;
        }
        const h = Math.floor(remaining / 3600000);
        const m = Math.floor((remaining % 3600000) / 60000);
        const s = Math.floor((remaining % 60000) / 1000);
        el.textContent = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
@endpush
