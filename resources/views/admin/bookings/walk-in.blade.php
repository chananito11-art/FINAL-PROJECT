@extends('layouts.app')
@section('title','New Walk-in Booking')
@section('page-title','Walk-in Booking')
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:24px}
    .card-footer{display:flex;justify-content:flex-end;gap:12px;padding:20px;background:var(--ghost-bg);border-top:1px solid var(--line)}
    @media(max-width:768px){.form-grid{grid-template-columns:1fr}}

    /* Guest Search */
    .guest-search-wrap{position:relative}
    .guest-results{position:absolute;top:calc(100% + 4px);left:0;right:0;background:var(--card-bg);border:1px solid rgba(255,107,0,.3);border-radius:12px;z-index:50;max-height:240px;overflow-y:auto;box-shadow:0 8px 24px rgba(0,0,0,.3);display:none}
    .guest-results.open{display:block}
    .guest-result-item{padding:12px 16px;cursor:pointer;border-bottom:1px solid var(--line);transition:background .15s}
    .guest-result-item:last-child{border-bottom:none}
    .guest-result-item:hover{background:rgba(255,107,0,.08)}
    .guest-result-item .g-name{font-weight:700;font-size:.9rem}
    .guest-result-item .g-meta{font-size:.78rem;color:var(--muted);margin-top:2px}
    .guest-selected-badge{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);border-radius:10px;padding:10px 14px;display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;font-size:.88rem}
    .guest-selected-badge .g-clear{cursor:pointer;color:var(--muted);font-size:.85rem;text-decoration:underline}

    .flatpickr-day.flatpickr-disabled.date-booked { background: rgba(255,107,0,.12); color: #ff6b00 !important; }
    .flatpickr-day.flatpickr-disabled.date-pending { background: rgba(59,130,246,.12); color: #3b82f6 !important; }
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
</style>
@endpush
@section('content')
<div style="max-width:1000px;margin:0 auto">
    <form action="{{ route('admin.bookings.walk-in.store') }}" method="POST">
        @csrf
        {{-- Hidden guest_profile_id — filled by JS on selection --}}
        <input type="hidden" name="guest_profile_id" id="guestProfileId" value="">

        <div class="card" style="margin-bottom:24px">
            <div class="card-header">
                <span class="card-title">Customer Information</span>
                <span style="font-size:.82rem;color:var(--muted)">Search for a returning customer or fill in new details</span>
            </div>
            <div style="padding:24px">

                {{-- Live Guest Search --}}
                <div class="form-group" style="margin-bottom:20px">
                    <label class="form-label">🔍 Search Existing Guest</label>
                    <div class="guest-search-wrap">
                        <input type="text" id="guestSearch" class="form-control"
                               placeholder="Type name, phone, or email…"
                               autocomplete="off">
                        <div class="guest-results" id="guestResults"></div>
                    </div>
                    <p style="font-size:.75rem;color:var(--muted);margin-top:4px">
                        If found, click the result to pre-fill the form. Otherwise fill in new details below.
                    </p>
                </div>

                {{-- Selected Badge --}}
                <div class="guest-selected-badge" id="guestSelectedBadge" style="display:none">
                    <div>
                        <span style="color:#4ade80;margin-right:6px">✓</span>
                        <strong id="guestSelectedName"></strong>
                        <span id="guestSelectedMeta" style="color:var(--muted);margin-left:8px;font-size:.8rem"></span>
                    </div>
                    <span class="g-clear" id="guestClear">Clear selection</span>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">First Name <span style="color:var(--red)">*</span></label>
                        <input type="text" name="first_name" id="fFirstName" class="form-control" required value="{{ old('first_name') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name <span style="color:var(--red)">*</span></label>
                        <input type="text" name="last_name" id="fLastName" class="form-control" required value="{{ old('last_name') }}">
                    </div>
                </div>
                <div class="form-grid" style="margin-top:16px">
                    <div class="form-group">
                        <label class="form-label">Email Address <span style="font-size:.75rem;color:var(--muted)">(optional)</span></label>
                        <input type="email" name="email" id="fEmail" class="form-control" value="{{ old('email') }}" placeholder="Leave blank for cash customers">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" id="fPhone" class="form-control" value="{{ old('phone') }}">
                    </div>
                </div>
                <div class="form-group" style="margin-top:16px">
                    <label class="form-label">Driver's License Number</label>
                    <input type="text" name="drivers_license_number" id="fLicense" class="form-control" value="{{ old('drivers_license_number') }}">
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom:24px">
            <div class="card-header"><span class="card-title">Rental Details</span></div>
            <div style="padding:24px">
                <div class="form-group">
                    <label class="form-label">Select Vehicle</label>
                    <select name="vehicle_id" id="vehicle_id" class="form-control" required>
                        <option value="">-- Select a Vehicle --</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}" data-price="{{ $v->price_per_day }}" {{ old('vehicle_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->brand }} {{ $v->name }} (PHP {{ number_format($v->price_per_day, 0) }}/day)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-grid" style="margin-top:16px">
                    <div class="form-group">
                        <label class="form-label">Pickup Date</label>
                        <input type="text" name="pickup_date" id="pickup_date" class="form-control datepicker" required value="{{ old('pickup_date') }}" placeholder="YYYY-MM-DD" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Return Date</label>
                        <input type="text" name="return_date" id="return_date" class="form-control datepicker" required value="{{ old('return_date') }}" placeholder="YYYY-MM-DD" autocomplete="off">
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">Payment Information</span></div>
            <div style="padding:24px">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Initial Payment (Optional Cash)</label>
                        <input type="number" name="initial_payment" class="form-control" step="0.01" min="0" value="{{ old('initial_payment', 0) }}">
                        <p style="font-size:.75rem;color:var(--text-dim);margin-top:4px">Record initial cash deposit if any.</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Security Deposit (Required Cash)</label>
                        <input type="number" name="security_deposit" class="form-control" step="0.01" min="0" value="{{ old('security_deposit', 3000) }}">
                        <p style="font-size:.75rem;color:var(--text-dim);margin-top:4px">Standard deposit: ₱3,000. Refundable upon return.</p>
                    </div>
                </div>
                <div style="margin-top:16px;border-top:1px solid var(--line);padding-top:16px">
                    <label class="form-label">Calculated Total Amount</label>
                    <div id="total_display" style="font-size:1.4rem;font-weight:800;color:var(--orange);margin-top:8px">PHP 0.00</div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Booking</button>
            </div>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // ── Date & Total Calculation (unchanged) ──────────────────────────────────
    const vehicleSelect  = document.getElementById('vehicle_id');
    const pickupInput    = document.getElementById('pickup_date');
    const returnInput    = document.getElementById('return_date');
    const totalDisplay   = document.getElementById('total_display');
    let fpPickup, fpReturn;

    function calculateTotal() {
        const option = vehicleSelect.options[vehicleSelect.selectedIndex];
        if (!option || !option.value || !pickupInput.value || !returnInput.value) { totalDisplay.textContent = 'PHP 0.00'; return; }
        const price = parseFloat(option.dataset.price);
        const start = new Date(pickupInput.value), end = new Date(returnInput.value);
        if (end <= start) { totalDisplay.textContent = 'Invalid Dates'; return; }
        const days = Math.max(1, Math.round((end - start) / 86400000));
        totalDisplay.textContent = 'PHP ' + (price * days).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
    }

    let datesStatusMap = [];

    async function updateAvailability() {
        const vehicleId = vehicleSelect.value;
        if (!vehicleId) { fpPickup.set('disable',[]); fpReturn.set('disable',[]); return; }
        try {
            const ranges = await (await fetch(`/vehicles/${vehicleId}/availability`)).json();
            datesStatusMap = [];
            const cfg = ranges.map(r => {
                const fromStr = r.pickup_date.substring(0, 10);
                const toStr = r.return_date.substring(0, 10);
                const bufferStr = r.buffer_date.substring(0, 10);
                
                const isPending = ['awaiting_approval', 'pending_payment', 'awaiting_verification'].includes(r.status);
                
                // Add the actual booked range
                datesStatusMap.push({ from: fromStr, to: toStr, type: isPending ? 'pending' : 'booked' });
                // Add the turnaround buffer date
                datesStatusMap.push({ from: bufferStr, to: bufferStr, type: 'turnaround' });

                return { from: fromStr, to: bufferStr };
            });
            fpPickup.set('disable', cfg); fpReturn.set('disable', cfg);
            pickupInput.value = ''; returnInput.value = ''; calculateTotal();
        } catch(e) { console.error(e); }
    }

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

    fpPickup = flatpickr('#pickup_date', { minDate:'today', dateFormat:'Y-m-d', onDayCreate: applyColors, onChange:(d,s) => { fpReturn.set('minDate',s); calculateTotal(); }});
    fpReturn = flatpickr('#return_date', { minDate:'today', dateFormat:'Y-m-d', onDayCreate: applyColors, onChange: calculateTotal });
    vehicleSelect.addEventListener('change', () => { updateAvailability(); calculateTotal(); });
    if (vehicleSelect.value) updateAvailability();

    // ── Live Guest Search ─────────────────────────────────────────────────────
    const searchInput   = document.getElementById('guestSearch');
    const resultsBox    = document.getElementById('guestResults');
    const profileIdInput = document.getElementById('guestProfileId');
    const selectedBadge = document.getElementById('guestSelectedBadge');
    const selectedName  = document.getElementById('guestSelectedName');
    const selectedMeta  = document.getElementById('guestSelectedMeta');
    const clearBtn      = document.getElementById('guestClear');

    const fFirst   = document.getElementById('fFirstName');
    const fLast    = document.getElementById('fLastName');
    const fEmail   = document.getElementById('fEmail');
    const fPhone   = document.getElementById('fPhone');
    const fLicense = document.getElementById('fLicense');

    let searchTimeout;

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        const q = searchInput.value.trim();
        if (q.length < 2) { resultsBox.innerHTML = ''; resultsBox.classList.remove('open'); return; }
        searchTimeout = setTimeout(async () => {
            const res = await fetch(`{{ route('admin.guests.search') }}?q=${encodeURIComponent(q)}`);
            const guests = await res.json();
            if (!guests.length) {
                resultsBox.innerHTML = `<div class="guest-result-item" style="color:var(--muted);cursor:default">No existing guests found — fill in details below</div>`;
            } else {
                resultsBox.innerHTML = guests.map(g => `
                    <div class="guest-result-item" data-id="${g.id}" data-first="${g.first_name}" data-last="${g.last_name}"
                         data-email="${g.email||''}" data-phone="${g.phone||''}" data-license="${g.drivers_license_number||''}"
                         data-count="${g.bookings_count}">
                        <div class="g-name">${g.first_name} ${g.last_name}</div>
                        <div class="g-meta">${g.phone||'—'} · ${g.email||'No email'} · ${g.bookings_count} booking(s)</div>
                    </div>`).join('');
            }
            resultsBox.classList.add('open');

            // Attach click handlers
            resultsBox.querySelectorAll('.guest-result-item[data-id]').forEach(item => {
                item.addEventListener('click', () => selectGuest(item.dataset));
            });
        }, 300);
    });

    function selectGuest(d) {
        profileIdInput.value = d.id;
        fFirst.value   = d.first;
        fLast.value    = d.last;
        fEmail.value   = d.email;
        fPhone.value   = d.phone;
        fLicense.value = d.license;
        selectedName.textContent = `${d.first} ${d.last}`;
        selectedMeta.textContent = `${d.count} previous booking(s) · ${d.phone || 'no phone'}`;
        selectedBadge.style.display = 'flex';
        searchInput.value = '';
        resultsBox.classList.remove('open');
    }

    clearBtn.addEventListener('click', () => {
        profileIdInput.value = '';
        fFirst.value = fLast.value = fEmail.value = fPhone.value = fLicense.value = '';
        selectedBadge.style.display = 'none';
    });

    // Close dropdown on outside click
    document.addEventListener('click', e => {
        if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
            resultsBox.classList.remove('open');
        }
    });
</script>
@endpush
