@extends('layouts.customer')
@section('title','Book ' . $vehicle->name)
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-day.flatpickr-disabled.date-booked { background: rgba(255,107,0,.15); color: #ff6b00 !important; }
    .flatpickr-day.flatpickr-disabled.date-pending { background: rgba(59,130,246,.15); color: #3b82f6 !important; }
    .flatpickr-day.flatpickr-disabled.date-turnaround { 
        background: repeating-linear-gradient(45deg, rgba(255,255,255,0.03), rgba(255,255,255,0.03) 10px, rgba(255,255,255,0.08) 10px, rgba(255,255,255,0.08) 20px) !important;
        color: rgba(240,242,255,0.3) !important; 
        border-color: rgba(255,255,255,0.08) !important;
        text-decoration: line-through;
    }
    body.light-mode .flatpickr-day.flatpickr-disabled.date-turnaround { 
        background: repeating-linear-gradient(45deg, rgba(0,0,0,0.02), rgba(0,0,0,0.02) 10px, rgba(0,0,0,0.05) 10px, rgba(0,0,0,0.05) 20px) !important;
        color: rgba(17,24,39,0.3) !important; 
        border-color: rgba(0,0,0,0.05) !important;
    }
    .booking-wrap{max-width:900px;margin:40px auto;padding:0 20px 60px}
    .booking-wrap h1{font-size:1.6rem;font-weight:900;letter-spacing:-.04em;margin-bottom:4px}
    .booking-wrap>.sub{color:var(--muted);margin-bottom:28px}
    .grid2{display:grid;grid-template-columns:1fr 1.5fr;gap:28px}
    .form-card{background:var(--card-bg);border:1px solid var(--line);border-radius:18px;padding:28px}
    .form-card h2{font-size:1.05rem;font-weight:800;margin-bottom:20px}
    .form-group{margin-bottom:16px}
    .form-label{display:block;font-size:.8rem;font-weight:600;color:var(--text-dim);margin-bottom:6px;text-transform:uppercase;letter-spacing:.04em}
    .form-control{width:100%;height:44px;background:var(--input-bg);border:1px solid var(--line);border-radius:10px;color:var(--text);font-family:inherit;font-size:.92rem;padding:0 13px;outline:none;transition:border-color .2s}
    .form-control:focus{border-color:rgba(255,107,0,.5);box-shadow:0 0 0 3px rgba(255,107,0,.12)}
    .g2{display:grid;grid-template-columns:1fr 1fr;gap:13px}
    .v-summary{background:rgba(255,107,0,.07);border:1px solid rgba(255,107,0,.18);border-radius:16px;padding:20px;position:sticky;top:80px}
    .v-summary img{width:100%;aspect-ratio:16/9;object-fit:cover;border-radius:12px;margin-bottom:16px}
    .v-sum-name{font-size:1.05rem;font-weight:800;letter-spacing:-.02em;margin-bottom:4px}
    .v-sum-type{font-size:.85rem;color:var(--text-dim);margin-bottom:16px}
    .price-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--line);font-size:.9rem}
    .price-row:last-child{border-bottom:none;font-weight:800;font-size:1rem}
    .price-row span:last-child{color:#ff8c3a}
    .terms-box{background:var(--hover-bg);border:1px solid var(--line);border-radius:12px;padding:16px;max-height:200px;overflow-y:auto;font-size:.83rem;color:var(--muted);line-height:1.6;margin-bottom:14px}
    .terms-box h2,.terms-box h3{color:var(--text);margin-bottom:8px;font-size:1rem}
    .check-row{display:flex;align-items:flex-start;gap:10px;font-size:.88rem;color:var(--muted);margin-bottom:20px}
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
        <form method="POST" action="{{ route('customer.booking.store') }}" id="bookingForm">
            @csrf
            <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
            
            {{-- STEP 1: Rental Dates (Full Width Top) --}}
            <div class="form-card" style="margin-bottom:28px; border: 1px solid rgba(255,107,0,0.3); background: rgba(255,107,0,0.02);">
                <h2 style="color:#ff6b00">📅 1. Select Rental Dates</h2>
                <div class="g2">
                    <div class="form-group">
                        <label class="form-label">Pickup Date</label>
                        <input type="text" name="pickup_date" id="pickupDate" class="form-control"
                               value="{{ old('pickup_date', request('pickup')) }}"
                               required placeholder="Select date..." autocomplete="off" readonly>
                        @error('pickup_date')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Return Date</label>
                        <input type="text" name="return_date" id="returnDate" class="form-control"
                               value="{{ old('return_date', request('return')) }}"
                               required placeholder="Select date..." autocomplete="off" readonly>
                        @error('return_date')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div style="background:var(--hover-bg);border-radius:10px;padding:12px 16px;font-size:.95rem;color:var(--text);border:1px dashed var(--line)">
                    Estimated Total: <strong id="totalCalc" style="color:#ff6b00; font-size: 1.1rem">—</strong>
                    <span id="pricingNote" style="display:none;font-size:.78rem;color:var(--muted);margin-left:8px"></span>
                </div>
            </div>

            <div class="grid2">
                <div>
                    <div class="v-summary">
                        <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->name }}">
                        <div class="v-sum-name">{{ $vehicle->name }}</div>
                        <div class="v-sum-type">{{ $vehicle->type }} · {{ $vehicle->transmission }}</div>
                        <div class="price-row"><span>Daily Rate</span><span id="baseDisplay">PHP {{ number_format($vehicle->price_per_day,0) }}</span></div>
                        <div class="price-row"><span>Duration</span><span id="daysDisplay">— days</span></div>
                        <div class="price-row"><span>Base Subtotal</span><span id="subtotalDisplay">—</span></div>
                        <div class="price-row" id="dynamicAdjRow" style="display:none;">
                            <span id="dynamicAdjLabel" style="color:var(--muted)">Dynamic Adjustment</span>
                            <span id="dynamicAdjValue" style="color:var(--orange-l)">—</span>
                        </div>
                        <div class="price-row" style="margin-top:12px"><span>Smart Total</span><span id="totalDisplay" style="font-size:1.1rem; color:var(--orange-l);">—</span></div>
                        <div id="pricingNote" style="display:none; font-size:.8rem; color:#22c55e; margin-top:8px; text-align:right; font-weight:600;"></div>
                    </div>
                </div>

                <div>
                    {{-- Personal Info & Other steps column --}}
                    <div class="form-card" style="margin-bottom:20px">
                        <h2>👤 2. Personal Information</h2>
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
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', auth()->user()->phone) }}" required>
                        </div>
                    </div>

                    <div class="form-card" style="margin-bottom:20px">
                        <h2>🏷️ 3. Discounts & Extras</h2>
                        <div class="form-group">
                            <label class="form-label">Discount Code</label>
                            <input type="text" name="discount_code" class="form-control" placeholder="Enter code (if any)" value="{{ old('discount_code') }}">
                            <p style="font-size:.75rem;color:var(--muted);margin-top:6px">Apply a code to get a reduction on your total price.</p>
                        </div>
                    </div>

                    <div class="form-card" style="margin-bottom:20px">
                        <h2>📋 4. Terms & Conditions</h2>
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
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
const pricePerDay={{ $vehicle->price_per_day }};
const vehicleId={{ $vehicle->id }};
const pickup=document.getElementById('pickupDate'),ret=document.getElementById('returnDate');
const daysD=document.getElementById('daysDisplay'),totalD=document.getElementById('totalDisplay'),totalC=document.getElementById('totalCalc');
const baseD=document.getElementById('baseDisplay');
const subtotalD=document.getElementById('subtotalDisplay');
const dynamicAdjRow=document.getElementById('dynamicAdjRow');
const dynamicAdjLabel=document.getElementById('dynamicAdjLabel');
const dynamicAdjValue=document.getElementById('dynamicAdjValue');
const pricingNote=document.getElementById('pricingNote');
const topPricingNote=document.getElementById('topPricingNote'); // fallback

let pricingTimeout=null;

function formatPHP(n){ return 'PHP '+Math.round(n).toLocaleString('en-PH'); }

function calc(){
    if(!pickup.value||!ret.value)return;
    const d=Math.max(1,Math.round((new Date(ret.value)-new Date(pickup.value))/(1000*60*60*24)));
    const baseSub = d * pricePerDay;

    // Instant UI feedback (fallback)
    daysD.textContent=d+' day'+(d!==1?'s':'');
    baseD.textContent=formatPHP(pricePerDay);
    subtotalD.textContent=formatPHP(baseSub);
    totalD.textContent=formatPHP(baseSub);
    totalC.textContent=formatPHP(baseSub);
    dynamicAdjRow.style.display='none';
    pricingNote.style.display='none';

    // Debounce the API call
    clearTimeout(pricingTimeout);
    pricingTimeout = setTimeout(function(){
        fetch(`/vehicles/${vehicleId}/pricing-preview?pickup_date=${pickup.value}&return_date=${ret.value}`)
            .then(r => r.json())
            .then(data => {
                baseD.textContent = formatPHP(pricePerDay);
                daysD.textContent = data.days+' day'+(data.days!==1?'s':'');
                subtotalD.textContent = formatPHP(data.base_price);
                
                totalD.textContent = formatPHP(data.final_price);
                totalC.textContent = formatPHP(data.final_price);

                let combined = data.combined_multiplier || (data.demand_multiplier * data.timeline_multiplier * data.availability_multiplier);
                
                if (combined > 1.01) {
                    let extra = data.final_price - data.base_price;
                    dynamicAdjLabel.textContent = 'Dynamic Demand Adjustment';
                    dynamicAdjValue.textContent = '+ ' + formatPHP(extra);
                    dynamicAdjValue.style.color = '#ef4444'; // red
                    dynamicAdjRow.style.display = 'flex';
                    pricingNote.style.display = 'none';
                } else if (combined < 0.99) {
                    let discount = data.base_price - data.final_price;
                    dynamicAdjLabel.textContent = 'Early Bird Discount';
                    dynamicAdjValue.textContent = '- ' + formatPHP(discount);
                    dynamicAdjValue.style.color = '#22c55e'; // green
                    dynamicAdjRow.style.display = 'flex';
                    pricingNote.textContent = '🎉 Includes Early Bird Discount!';
                    pricingNote.style.display = 'block';
                } else {
                    dynamicAdjRow.style.display = 'none';
                    pricingNote.style.display = 'none';
                }
            })
            .catch(() => {
                // On error, just stick with the fallback calculations
                dynamicAdjRow.style.display='none';
                pricingNote.style.display='none';
            });
    }, 400);
}

document.addEventListener('DOMContentLoaded', function() {
    fetch("{{ route('vehicles.availability', $vehicle->id) }}")
        .then(response => response.json())
        .then(data => {
            let datesStatusMap = [];
            let disabledDates = data.map(range => {
                let fromStr = range.pickup_date.substring(0, 10);
                let toStr = range.return_date.substring(0, 10);
                let bufferStr = range.buffer_date.substring(0, 10);
                
                let isPending = ['awaiting_approval', 'pending_payment', 'awaiting_verification'].includes(range.status);
                
                // Add the actual booked range
                datesStatusMap.push({ from: fromStr, to: toStr, type: isPending ? 'pending' : 'booked' });
                // Add the turnaround buffer date
                datesStatusMap.push({ from: bufferStr, to: bufferStr, type: 'turnaround' });

                return { from: fromStr, to: bufferStr };
            });

            function applyColors(dObj, dStr, fp, dayElem) {
                for (let range of datesStatusMap) {
                    let d = dayElem.dateObj.getTime();
                    let start = new Date(range.from + "T00:00:00").getTime();
                    let end = new Date(range.to + "T00:00:00").getTime();
                    if (d >= start && d <= end) {
                        if (range.type === 'turnaround') {
                            dayElem.classList.add('date-turnaround');
                            dayElem.title = 'Preventive Maintenance & Turnaround Day';
                        } else {
                            let isBooked = range.type === 'booked';
                            dayElem.classList.add(isBooked ? 'date-booked' : 'date-pending');
                            dayElem.title = isBooked ? 'This date is fully booked.' : 'This date is currently on hold.';
                        }
                        break;
                    }
                }
            }

            let retPicker = flatpickr(ret, {
                minDate: "today",
                disable: disabledDates,
                dateFormat: "Y-m-d",
                defaultDate: "{{ request('return') ?: old('return_date') }}",
                onChange: calc,
                onDayCreate: applyColors
            });

            flatpickr(pickup, {
                minDate: "today",
                disable: disabledDates,
                dateFormat: "Y-m-d",
                defaultDate: "{{ request('pickup') ?: old('pickup_date') }}",
                onDayCreate: applyColors,
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length > 0) {
                        retPicker.set('minDate', selectedDates[0]);
                    }
                    calc();
                }
            });

            // Trigger calculation if dates are pre-filled
            setTimeout(calc, 500);
        });
});

document.getElementById('bookingForm').addEventListener('submit',function(){
    var b=document.getElementById('submitBtn');b.disabled=true;b.textContent='Processing…';
});
</script>
@endpush
