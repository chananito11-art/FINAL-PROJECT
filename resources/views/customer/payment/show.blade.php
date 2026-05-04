@extends('layouts.customer')
@section('title','GCash Payment — Booking #' . $booking->id)
@push('styles')
<style>
    .pay-wrap{max-width:680px;margin:48px auto;padding:0 20px 80px}
    .pay-wrap h1{font-size:1.6rem;font-weight:900;letter-spacing:-.04em;margin-bottom:4px}
    .pay-wrap>.sub{color:rgba(240,242,255,.55);margin-bottom:28px}
    .info-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:22px;margin-bottom:20px}
    .info-card h2{font-size:1rem;font-weight:800;margin-bottom:16px}
    .gcash-box{background:linear-gradient(135deg,rgba(0,103,255,.15),rgba(0,103,255,.06));border:1px solid rgba(0,103,255,.25);border-radius:14px;padding:24px;text-align:center;margin-bottom:20px}
    .gcash-logo{font-size:1.4rem;font-weight:900;color:#4d8eff;letter-spacing:-.04em;margin-bottom:8px}
    .gcash-num{font-size:2rem;font-weight:900;letter-spacing:.08em;color:#f0f2ff;margin-bottom:4px}
    .gcash-name{font-size:.9rem;color:rgba(240,242,255,.55)}
    .amount-chip{display:inline-block;background:rgba(255,107,0,.15);border:1px solid rgba(255,107,0,.25);color:#ff8c3a;font-size:1.2rem;font-weight:900;padding:10px 24px;border-radius:12px;margin:16px 0}
    .detail-row{display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid rgba(255,255,255,.05);font-size:.9rem;color:rgba(240,242,255,.7)}
    .detail-row:last-child{border-bottom:none}
    .detail-row strong{color:#f0f2ff}
    .form-group{margin-bottom:16px}
    .form-label{display:block;font-size:.8rem;font-weight:600;color:rgba(240,242,255,.5);margin-bottom:6px;text-transform:uppercase;letter-spacing:.04em}
    .form-control{width:100%;height:44px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#f0f2ff;font-family:inherit;font-size:.92rem;padding:0 13px;outline:none;transition:border-color .2s}
    .form-control:focus{border-color:rgba(255,107,0,.5);box-shadow:0 0 0 3px rgba(255,107,0,.12)}
    .upload-zone{border:2px dashed rgba(255,255,255,.15);border-radius:12px;padding:28px;text-align:center;color:rgba(240,242,255,.5);cursor:pointer;transition:border-color .2s}
    .upload-zone:hover{border-color:rgba(255,107,0,.4)}
    .upload-zone input{display:none}
    .submit-btn{width:100%;height:50px;background:linear-gradient(135deg,#ff8c3a,#ff6b00);border:none;border-radius:12px;color:#fff;font-family:inherit;font-size:1rem;font-weight:700;cursor:pointer;box-shadow:0 6px 20px rgba(255,107,0,.3);transition:filter .2s,transform .2s;margin-top:8px}
    .submit-btn:hover{filter:brightness(1.08);transform:translateY(-2px)}
    #preview{display:none;width:100%;border-radius:10px;margin-top:12px;max-height:240px;object-fit:contain}
</style>
@endpush
@section('content')
<div class="pay-wrap">
    <h1>Complete Your Payment</h1>
    <p class="sub">Booking #{{ $booking->id }} · {{ $booking->vehicle->name }}</p>

    <div class="gcash-box">
        <div class="gcash-logo">GCash</div>
        <div class="gcash-num">0917 123 4567</div>
        <div class="gcash-name">OrangeCrush Car Rentals</div>
        <div class="amount-chip">PHP {{ number_format($booking->total_amount, 0) }}</div>
        <p style="font-size:.83rem;color:rgba(240,242,255,.45)">Send the exact amount above, then fill the form below.</p>
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
                <label class="form-label">GCash Reference Code</label>
                <input type="text" name="reference_code" class="form-control" placeholder="e.g. GC-2024-001234" value="{{ old('reference_code') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Screenshot / Receipt</label>
                <label class="upload-zone" id="uploadZone">
                    <input type="file" name="screenshot" id="screenshotInput" accept="image/*" required>
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <p style="margin-top:10px;font-size:.88rem" id="uploadLabel">Click to upload or drag & drop<br><span style="font-size:.78rem">PNG, JPG up to 5 MB</span></p>
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
document.getElementById('screenshotInput').addEventListener('change',function(){
    const f=this.files[0];if(!f)return;
    document.getElementById('uploadLabel').textContent=f.name;
    const p=document.getElementById('preview');
    p.src=URL.createObjectURL(f);p.style.display='block';
});
document.getElementById('payForm').addEventListener('submit',function(){
    var b=document.getElementById('payBtn');b.disabled=true;b.textContent='Submitting…';
});
</script>
@endpush
