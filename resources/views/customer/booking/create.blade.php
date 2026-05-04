@extends('layouts.customer')
@section('title','Book ' . $vehicle->name)
@push('styles')
<style>
    .booking-wrap{max-width:900px;margin:40px auto;padding:0 20px 60px}
    .booking-wrap h1{font-size:1.6rem;font-weight:900;letter-spacing:-.04em;margin-bottom:4px}
    .booking-wrap>.sub{color:rgba(240,242,255,.55);margin-bottom:28px}
    .grid2{display:grid;grid-template-columns:1.2fr 1fr;gap:28px}
    .form-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:18px;padding:28px}
    .form-card h2{font-size:1.05rem;font-weight:800;margin-bottom:20px}
    .form-group{margin-bottom:16px}
    .form-label{display:block;font-size:.8rem;font-weight:600;color:rgba(240,242,255,.5);margin-bottom:6px;text-transform:uppercase;letter-spacing:.04em}
    .form-control{width:100%;height:44px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#f0f2ff;font-family:inherit;font-size:.92rem;padding:0 13px;outline:none;transition:border-color .2s}
    .form-control:focus{border-color:rgba(255,107,0,.5);box-shadow:0 0 0 3px rgba(255,107,0,.12)}
    .g2{display:grid;grid-template-columns:1fr 1fr;gap:13px}
    .v-summary{background:rgba(255,107,0,.07);border:1px solid rgba(255,107,0,.18);border-radius:16px;padding:20px;position:sticky;top:80px}
    .v-summary img{width:100%;aspect-ratio:16/9;object-fit:cover;border-radius:12px;margin-bottom:16px}
    .v-sum-name{font-size:1.05rem;font-weight:800;letter-spacing:-.02em;margin-bottom:4px}
    .v-sum-type{font-size:.85rem;color:rgba(240,242,255,.5);margin-bottom:16px}
    .price-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.06);font-size:.9rem}
    .price-row:last-child{border-bottom:none;font-weight:800;font-size:1rem}
    .price-row span:last-child{color:#ff8c3a}
    .terms-box{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:16px;max-height:200px;overflow-y:auto;font-size:.83rem;color:rgba(240,242,255,.6);line-height:1.6;margin-bottom:14px}
    .terms-box h2,.terms-box h3{color:#f0f2ff;margin-bottom:8px;font-size:1rem}
    .check-row{display:flex;align-items:flex-start;gap:10px;font-size:.88rem;color:rgba(240,242,255,.7);margin-bottom:20px}
    .check-row input{accent-color:#ff6b00;margin-top:2px;flex-shrink:0}
    .submit-btn{width:100%;height:50px;background:linear-gradient(135deg,#ff8c3a,#ff6b00);border:none;border-radius:12px;color:#fff;font-family:inherit;font-size:1rem;font-weight:700;cursor:pointer;box-shadow:0 6px 20px rgba(255,107,0,.3);transition:filter .2s,transform .2s}
    .submit-btn:hover{filter:brightness(1.08);transform:translateY(-2px)}
    .error-msg{font-size:.8rem;color:#ff8080;margin-top:4px}
    @media(max-width:760px){.grid2{grid-template-columns:1fr}}
</style>
@endpush
@section('content')
<div class="booking-wrap">
    <h1>Book Your Vehicle</h1>
    <p class="sub">Fill in your details to reserve {{ $vehicle->name }}</p>
    @if($errors->any())
        <div style="background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);border-radius:10px;padding:12px 16px;color:#f87171;margin-bottom:20px;font-size:.9rem">{{ $errors->first() }}</div>
    @endif
    <div class="grid2">
        <form method="POST" action="{{ route('customer.booking.store') }}" id="bookingForm">
            @csrf
            <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
            <div class="form-card" style="margin-bottom:20px">
                <h2>Personal Information</h2>
                <div class="g2">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name', auth()->user()->first_name) }}" required>
                        @error('first_name')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name', auth()->user()->last_name) }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
                </div>
                <div class="g2">
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', auth()->user()->phone) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Driver's License #</label>
                        <input type="text" name="drivers_license_number" class="form-control" value="{{ old('drivers_license_number') }}" required>
                        @error('drivers_license_number')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
            <div class="form-card" style="margin-bottom:20px">
                <h2>Rental Dates</h2>
                <div class="g2">
                    <div class="form-group">
                        <label class="form-label">Pickup Date</label>
                        <input type="date" name="pickup_date" id="pickupDate" class="form-control" value="{{ old('pickup_date') }}" min="{{ date('Y-m-d') }}" required>
                        @error('pickup_date')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Return Date</label>
                        <input type="date" name="return_date" id="returnDate" class="form-control" value="{{ old('return_date') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                        @error('return_date')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div style="background:rgba(255,255,255,.04);border-radius:10px;padding:12px 16px;font-size:.88rem;color:rgba(240,242,255,.6)">
                    Total: <strong id="totalCalc" style="color:#ff8c3a">—</strong>
                </div>
            </div>
            <div class="form-card">
                <h2>Terms & Conditions</h2>
                @if($terms)
                <div class="terms-box">{!! $terms->content !!}</div>
                @endif
                <div class="check-row">
                    <input type="checkbox" name="terms_agreed" id="termsCheck" value="1" required>
                    <label for="termsCheck">I have read and agree to the Terms & Conditions above.</label>
                </div>
                @error('terms_agreed')<p class="error-msg" style="margin-bottom:12px">{{ $message }}</p>@enderror
                <button type="submit" class="submit-btn" id="submitBtn">Proceed to Payment →</button>
            </div>
        </form>
        <div>
            <div class="v-summary">
                <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->name }}">
                <div class="v-sum-name">{{ $vehicle->name }}</div>
                <div class="v-sum-type">{{ $vehicle->type }} · {{ $vehicle->transmission }}</div>
                <div class="price-row"><span>Rate per day</span><span>PHP {{ number_format($vehicle->price_per_day,0) }}</span></div>
                <div class="price-row"><span>Duration</span><span id="daysDisplay">— days</span></div>
                <div class="price-row"><span>Total</span><span id="totalDisplay">—</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
const pricePerDay={{ $vehicle->price_per_day }};
const pickup=document.getElementById('pickupDate'),ret=document.getElementById('returnDate');
const daysD=document.getElementById('daysDisplay'),totalD=document.getElementById('totalDisplay'),totalC=document.getElementById('totalCalc');
function calc(){
    if(!pickup.value||!ret.value)return;
    const d=Math.max(1,Math.round((new Date(ret.value)-new Date(pickup.value))/(1000*60*60*24)));
    const t='PHP '+Math.round(d*pricePerDay).toLocaleString('en-PH');
    daysD.textContent=d+' day'+(d!==1?'s':'');
    totalD.textContent=t;totalC.textContent=t;
    ret.min=pickup.value;
}
pickup.addEventListener('change',calc);ret.addEventListener('change',calc);
document.getElementById('bookingForm').addEventListener('submit',function(){
    var b=document.getElementById('submitBtn');b.disabled=true;b.textContent='Processing…';
});
</script>
@endpush
