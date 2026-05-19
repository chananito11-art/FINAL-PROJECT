@extends('layouts.app')
@section('title','Booking #' . $booking->id)
@section('page-title','Booking #' . $booking->id)
@section('content')
<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:24px">
    <div>
        <div class="card" style="margin-bottom:16px">
            <div class="card-header">
                <span class="card-title">Booking Details</span>
                @php $map=['awaiting_approval'=>'bb','pending_payment'=>'by','awaiting_verification'=>'bb','partial_paid'=>'bo','fully_paid'=>'bg_','confirmed'=>'bg_','rejected'=>'br','ongoing'=>'bo','completed'=>'bgy','cancelled'=>'br']; @endphp
                <span class="badge {{ $map[$booking->status]??'bgy' }}">{{ ucwords(str_replace('_',' ',$booking->status)) }}</span>
            </div>
            <div class="card-body">
                <div style="display:grid;gap:10px;font-size:.9rem">
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)"><span style="color:var(--text-dim)">Customer</span><strong>{{ $booking->full_name }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)"><span style="color:var(--text-dim)">Pickup</span><strong>{{ $booking->pickup_date?->format('M d, Y') }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)"><span style="color:var(--text-dim)">Return</span><strong>{{ $booking->return_date?->format('M d, Y') }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)"><span style="color:var(--text-dim)">Total Amount</span><strong style="color:var(--text)">₱{{ number_format($booking->total_amount,2) }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)"><span style="color:var(--text-dim)">Paid Amount</span><strong style="color:var(--green)">₱{{ number_format($booking->paid_amount,2) }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)"><span style="color:var(--text-dim)">Balance</span><strong style="color:var(--orange-l);font-size:1.1rem">₱{{ number_format($booking->balance_amount,2) }}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)">
                        <span style="color:var(--text-dim)">Security Deposit</span>
                        <div style="text-align:right">
                            <strong style="color:var(--text)">₱{{ number_format($booking->security_deposit,2) }}</strong><br>
                            @php 
                                $depMap = [
                                    'pending' => 'by',
                                    'held' => 'bo',
                                    'released' => 'bg_',
                                    'held_for_deduction' => 'br',
                                    'settled' => 'bg_',
                                    'refunded' => 'bg_',
                                    'forfeited' => 'br'
                                ];
                            @endphp
                            <span class="badge {{ $depMap[$booking->security_deposit_status] ?? 'bgy' }}" style="font-size:0.7rem">
                                {{ ucwords(str_replace('_',' ',$booking->security_deposit_status)) }}
                            </span>
                        </div>
                    </div>
                    @if($booking->late_fee > 0 || $booking->refueling_fee > 0)
                    <div style="background:var(--ghost-bg); padding:10px; border-radius:8px; margin-top:8px">
                        <div style="font-size:0.75rem; color:var(--text-dim); margin-bottom:4px">Return Charges (To be deducted)</div>
                        @if($booking->late_fee > 0)<div style="display:flex;justify-content:space-between;font-size:0.85rem"><span>Late Fee</span><strong>₱{{ number_format($booking->late_fee, 2) }}</strong></div>@endif
                        @if($booking->refueling_fee > 0)<div style="display:flex;justify-content:space-between;font-size:0.85rem"><span>Refueling Fee</span><strong>₱{{ number_format($booking->refueling_fee, 2) }}</strong></div>@endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom:16px">
            <div class="card-header"><span class="card-title">Payment History</span></div>
            <div class="tw">
                <table>
                    <thead><tr><th>Date</th><th>Method</th><th>Amount</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($booking->payments()->latest()->get() as $p)
                        <tr>
                            <td style="font-size:.8rem">{{ $p->created_at->format('M d, Y H:i') }}</td>
                            <td style="text-transform:capitalize">{{ $p->payment_method ?: $p->method }}</td>
                            <td style="font-weight:600">₱{{ number_format($p->amount, 2) }}</td>
                            <td><span class="badge {{ $p->status==='verified'?'bg_':($p->status==='rejected'?'br':'by') }}">{{ ucfirst($p->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="text-align:center;padding:20px;color:var(--text-dim)">No payments recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($booking->payment && $booking->payment->screenshot_path)
        <div class="card" style="margin-bottom:16px">
            <div class="card-header"><span class="card-title">Latest GCash Proof</span></div>
            <div class="card-body">
                <img src="{{ $booking->payment->screenshot_url }}" style="width:100%;border-radius:10px;max-height:320px;object-fit:contain;background:#06091b">
            </div>
        </div>
        @endif
    </div>

    <div>
        @if(!in_array($booking->status, ['completed', 'cancelled', 'no_show']))
        <div class="card" style="margin-bottom:16px">
            <div class="card-header"><span class="card-title">Booking Actions</span></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:12px">
                
                @if($booking->status === 'awaiting_approval')
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Admin Notes (optional)</label>
                            <textarea name="admin_notes" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%">✓ Approve Request</button>
                    </form>
                    <form method="POST" action="{{ route('admin.bookings.reject', $booking) }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Rejection Reason</label>
                            <textarea name="rejection_reason" class="form-control" rows="2" required placeholder="Why is this being rejected?"></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger" style="width:100%">✕ Reject Request</button>
                    </form>
                </div>
                @endif

                @if(in_array($booking->status, ['partial_paid', 'fully_paid']))
                <form method="POST" action="{{ route('admin.bookings.confirm', $booking) }}">
                    @csrf
                    <button type="submit" class="btn btn-success" style="width:100%">✓ Confirm Booking (Reserve Car)</button>
                </form>
                @endif

                @if($booking->status === 'confirmed')
                    <div style="background:linear-gradient(135deg,rgba(255,107,0,.08),rgba(255,140,58,.04));border:1px solid rgba(255,107,0,.25);border-radius:14px;overflow:hidden">
                        <div style="padding:14px 18px;background:rgba(255,107,0,.1);border-bottom:1px solid rgba(255,107,0,.2);display:flex;align-items:center;gap:8px">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ff8c3a" stroke-width="2"><path d="M3 13l1.5-4.5A3 3 0 0 1 7.35 6h9.3a3 3 0 0 1 2.85 2.5L21 13"/><path d="M5 16h14"/><circle cx="7.5" cy="15.5" r="1.5"/><circle cx="16.5" cy="15.5" r="1.5"/></svg>
                            <span style="font-weight:800;font-size:.92rem;color:var(--orange-l)">Vehicle Handover Process</span>
                        </div>
                        <div style="padding:16px 18px;display:flex;flex-direction:column;gap:12px">

                            @if(!$booking->inspections()->where('type', 'pickup')->exists())
                            {{-- Step 1: Needs inspection --}}
                            <div style="border:2px solid rgba(255,107,0,.35);border-radius:12px;padding:14px 16px">
                                <div style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--orange-l);margin-bottom:8px">Step 1 — Pre-Dispatch Inspection</div>
                                <p style="font-size:.82rem;color:var(--muted);margin-bottom:12px">Record the vehicle's odometer, fuel level, and physical condition before handing over the keys to the customer.</p>
                                <a href="{{ route('admin.bookings.inspection.create', $booking) }}?type=pickup" class="btn btn-primary" style="width:100%">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:6px"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                    Conduct Pre-Dispatch Inspection
                                </a>
                            </div>
                            <div style="border:1px solid var(--line);border-radius:12px;padding:14px 16px;opacity:.4">
                                <div style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:4px">Step 2 — Hand Over Keys</div>
                                <p style="font-size:.82rem;color:var(--muted)">Complete Step 1 first to unlock this action.</p>
                            </div>

                            @else
                            {{-- Step 1 done --}}
                            <div style="border:1px solid rgba(34,197,94,.25);border-radius:12px;padding:12px 16px;background:rgba(34,197,94,.04)">
                                <div style="display:flex;align-items:center;gap:8px;font-size:.88rem;color:var(--green);font-weight:700;margin-bottom:8px">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                    Step 1 Complete — Pre-Dispatch Inspection Recorded
                                </div>
                                @php $pickupInsp = $booking->inspections()->where('type','pickup')->latest()->first(); @endphp
                                @if($pickupInsp)
                                <div style="font-size:.8rem;color:var(--muted);display:flex;gap:16px;flex-wrap:wrap">
                                    <span>Odometer: <strong>{{ number_format($pickupInsp->odometer_reading) }} km</strong></span>
                                    <span>Fuel: <strong>{{ $pickupInsp->fuel_level }}%</strong></span>
                                    <span>Exterior: <strong>{{ $pickupInsp->exterior_condition }}</strong></span>
                                </div>
                                @endif
                            </div>
                            {{-- Step 2: dispatch --}}
                            <div style="border:2px solid rgba(34,197,94,.3);border-radius:12px;padding:14px 16px;background:rgba(34,197,94,.03)">
                                <div style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--green);margin-bottom:8px">Step 2 — Hand Over Keys & Start Rental</div>
                                <p style="font-size:.82rem;color:var(--muted);margin-bottom:12px">This closes the booking and moves the vehicle to the <strong>Ongoing Rentals</strong> module.</p>
                                <form method="POST" action="{{ route('admin.bookings.ongoing', $booking) }}">@csrf
                                    <button type="submit" class="btn btn-success" style="width:100%" onclick="return confirm('Hand over the vehicle and start the rental?')">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:6px"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                                        Hand Over Keys — Start Rental
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- No-Show Panel (only shows on/after pickup date) --}}
                    @if(!$booking->pickup_date->isFuture())
                    <div style="background:rgba(239,68,68,.06); border-radius:12px; padding:16px; border:1px solid rgba(239,68,68,.2); margin-top:12px">
                        <div style="font-weight:700; margin-bottom:6px; font-size:.9rem; color:#f87171">Customer No-Show</div>
                        <p style="font-size:.82rem; color:var(--muted); margin-bottom:12px">
                            Pickup was scheduled for <strong>{{ $booking->pickup_date->format('M d, Y') }}</strong>. If the customer did not arrive, mark this booking as a No-Show.
                        </p>
                        <form method="POST" action="{{ route('admin.bookings.no-show', $booking) }}">
                            @csrf
                            <div class="form-group">
                                <label class="form-label">Notes (optional)</label>
                                <input type="text" name="notes" class="form-control" placeholder="e.g. Customer called to cancel…">
                            </div>
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px; font-size:.85rem; color:var(--muted)">
                                <input type="checkbox" name="forfeit_deposit" id="forfeitDeposit" value="1" style="accent-color:#f87171">
                                <label for="forfeitDeposit">Forfeit security deposit (₱{{ number_format($booking->security_deposit, 0) }})</label>
                            </div>
                            <button type="submit" class="btn btn-danger btn-sm" style="width:100%" onclick="return confirm('Mark this booking as No-Show? This cannot be undone.')">
                                Mark as No-Show
                            </button>
                        </form>
                    </div>
                    @endif
                @endif

                @if($booking->status === 'completed' && in_array($booking->security_deposit_status, ['held', 'held_for_deduction']))
                @php
                    $lateFee      = $booking->late_fee      ?? 0;
                    $refuelingFee = $booking->refueling_fee ?? 0;
                    $deductions   = $lateFee + $refuelingFee;
                    $deposit      = $booking->security_deposit;
                    $surplus      = max(0, $deposit - $deductions);
                    $deficit      = max(0, $deductions - $deposit);
                    // Auto-detect scenario
                    $scenario = $deductions <= 0 ? 'clean'
                              : ($deductions <= $deposit ? 'partial'
                              : 'excess');
                @endphp

                {{-- Settlement Card --}}
                <div style="background:rgba(255,107,0,.05);border:1px solid rgba(255,107,0,.25);border-radius:14px;overflow:hidden;margin-top:4px">

                    {{-- Header --}}
                    <div style="padding:14px 18px;background:rgba(255,107,0,.08);border-bottom:1px solid rgba(255,107,0,.2);display:flex;align-items:center;gap:8px">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ff8c3a" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        <span style="font-weight:800;font-size:.92rem;color:var(--orange-l)">Security Deposit Settlement</span>
                    </div>

                    {{-- Financial Breakdown --}}
                    <div style="padding:16px 18px;border-bottom:1px solid rgba(255,107,0,.12)">
                        <div style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:10px">Financial Summary</div>

                        <div style="display:flex;justify-content:space-between;font-size:.88rem;padding:6px 0">
                            <span style="color:var(--muted)">Security Deposit Held</span>
                            <strong>₱{{ number_format($deposit, 2) }}</strong>
                        </div>

                        @if($lateFee > 0)
                        <div style="display:flex;justify-content:space-between;font-size:.88rem;padding:6px 0;color:#f87171">
                            <span>Late Return Fee</span>
                            <strong>− ₱{{ number_format($lateFee, 2) }}</strong>
                        </div>
                        @endif

                        @if($refuelingFee > 0)
                        <div style="display:flex;justify-content:space-between;font-size:.88rem;padding:6px 0;color:#f87171">
                            <span>Refueling Fee</span>
                            <strong>− ₱{{ number_format($refuelingFee, 2) }}</strong>
                        </div>
                        @endif

                        <div style="border-top:1px solid var(--line);margin:8px 0"></div>

                        @if($scenario === 'clean')
                        <div style="display:flex;justify-content:space-between;font-size:.95rem;font-weight:800;color:#4ade80">
                            <span>Refund to Customer</span>
                            <span>₱{{ number_format($deposit, 2) }}</span>
                        </div>
                        <div style="margin-top:8px;font-size:.78rem;background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);border-radius:8px;padding:8px 12px;color:#4ade80">
                            ✓ No deductions — return full deposit to customer.
                        </div>

                        @elseif($scenario === 'partial')
                        <div style="display:flex;justify-content:space-between;font-size:.95rem;font-weight:800;color:#4ade80">
                            <span>Refund to Customer</span>
                            <span>₱{{ number_format($surplus, 2) }}</span>
                        </div>
                        <div style="margin-top:8px;font-size:.78rem;background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.2);border-radius:8px;padding:8px 12px;color:#fbbf24">
                            ⚠ Deduct ₱{{ number_format($deductions, 2) }} from deposit, refund the ₱{{ number_format($surplus, 2) }} remainder.
                        </div>

                        @else {{-- excess --}}
                        <div style="display:flex;justify-content:space-between;font-size:.95rem;font-weight:800;color:#f87171">
                            <span>Customer Still Owes</span>
                            <span>₱{{ number_format($deficit, 2) }}</span>
                        </div>
                        <div style="margin-top:8px;font-size:.78rem;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:8px;padding:8px 12px;color:#f87171">
                            🔴 Fees exceed deposit. Full deposit is consumed. Customer owes an additional ₱{{ number_format($deficit, 2) }}.
                        </div>
                        @endif
                    </div>

                    {{-- Settlement Form --}}
                    <form method="POST" action="{{ route('admin.bookings.settle-deposit', $booking) }}" style="padding:16px 18px">
                        @csrf
                        <input type="hidden" name="action" id="settlementAction"
                               value="{{ $scenario === 'clean' ? 'refund_full' : ($scenario === 'partial' ? 'deduct_and_refund' : 'excess_charge') }}">

                        {{-- Override Action (advanced) --}}
                        <details style="margin-bottom:12px">
                            <summary style="font-size:.78rem;color:var(--muted);cursor:pointer;user-select:none">Override settlement action</summary>
                            <div style="margin-top:10px">
                                <select class="form-control" id="settlementActionSelect" style="font-size:.85rem"
                                        onchange="document.getElementById('settlementAction').value=this.value;toggleSettlementFields(this.value)">
                                    <option value="refund_full" {{ $scenario==='clean' ? 'selected':'' }}>Refund Full Deposit</option>
                                    <option value="deduct_and_refund" {{ $scenario==='partial' ? 'selected':'' }}>Deduct & Refund Remainder</option>
                                    <option value="excess_charge" {{ $scenario==='excess' ? 'selected':'' }}>Excess Charges (Customer Owes More)</option>
                                    <option value="forfeit">Forfeit Full Deposit</option>
                                </select>
                            </div>
                        </details>

                        {{-- Refund amount (deduct_and_refund) --}}
                        <div id="refundAmountWrap" style="display:{{ $scenario==='partial' ? 'block' : 'none' }};margin-bottom:12px">
                            <label class="form-label">Actual Refund Amount</label>
                            <input type="number" name="refund_amount" class="form-control" step="0.01"
                                   value="{{ $surplus }}" min="0" max="{{ $deposit }}">
                        </div>

                        {{-- Extra charge (excess_charge) --}}
                        <div id="extraChargeWrap" style="display:{{ $scenario==='excess' ? 'block' : 'none' }};margin-bottom:12px">
                            <label class="form-label">Extra Amount Customer Owes</label>
                            <input type="number" name="extra_charge" class="form-control" step="0.01"
                                   value="{{ $deficit }}" min="0">
                        </div>

                        <div class="form-group" style="margin-bottom:14px">
                            <label class="form-label">Settlement Notes</label>
                            <textarea name="admin_notes" class="form-control" rows="2"
                                      placeholder="e.g. Refund via GCash to 09xx-xxx-xxxx…"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:100%"
                                onclick="return confirm('Process this settlement? This cannot be undone.')">
                            ✓ Process Settlement
                        </button>
                    </form>
                </div>

                <script>
                function toggleSettlementFields(action) {
                    document.getElementById('refundAmountWrap').style.display  = action === 'deduct_and_refund' ? 'block' : 'none';
                    document.getElementById('extraChargeWrap').style.display   = action === 'excess_charge'     ? 'block' : 'none';
                }
                </script>
                @endif


                @if($booking->status === 'completed' && $booking->rental)
                    <div style="background:rgba(34,197,94,.05); border-radius:12px; padding:16px; border:1px solid rgba(34,197,94,.2)">
                        <div style="font-weight:700; margin-bottom:4px; font-size:.9rem; color:var(--green)">Active Rental Contract</div>
                        <p style="font-size:.85rem; color:var(--text-dim); margin-bottom:12px">This booking has been converted into an active rental.</p>
                        <a href="{{ route('admin.returns.index') }}" class="btn btn-ghost btn-sm" style="width:100%">View in Returns Dashboard</a>
                    </div>
                @endif

                @if($booking->status === 'ongoing')
                    {{-- Phase 4: Prominent Ongoing Rental Banner --}}
                    <div style="background:linear-gradient(135deg,rgba(255,107,0,.1),rgba(255,140,58,.05));border:2px solid rgba(255,107,0,.3);border-radius:14px;overflow:hidden">
                        <div style="padding:14px 18px;background:rgba(255,107,0,.12);border-bottom:1px solid rgba(255,107,0,.2);display:flex;align-items:center;gap:8px">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ff8c3a" stroke-width="2"><path d="M3 13l1.5-4.5A3 3 0 0 1 7.35 6h9.3a3 3 0 0 1 2.85 2.5L21 13"/><path d="M5 16h14"/><circle cx="7.5" cy="15.5" r="1.5"/><circle cx="16.5" cy="15.5" r="1.5"/></svg>
                            <span style="font-weight:800;font-size:.92rem;color:var(--orange-l)">Vehicle Is Currently On The Road</span>
                            <span class="badge bo" style="margin-left:auto">Active Rental</span>
                        </div>
                        <div style="padding:16px 18px;display:flex;flex-direction:column;gap:10px">
                            <p style="font-size:.85rem;color:var(--muted)">This booking has been dispatched. The vehicle is now tracked in the <strong style="color:var(--orange-l)">Ongoing Rentals</strong> module.</p>
                            @if($booking->rental)
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:.82rem;background:var(--ghost-bg);border-radius:10px;padding:12px">
                                <div><span style="color:var(--muted)">Picked Up</span><br><strong>{{ $booking->rental->pickup_date?->format('M d, Y h:i A') }}</strong></div>
                                <div><span style="color:var(--muted)">Due Return</span><br><strong>{{ $booking->rental->expected_return_date?->format('M d, Y') }}</strong></div>
                                <div><span style="color:var(--muted)">Odometer at Pickup</span><br><strong>{{ number_format($booking->rental->pickup_odometer ?? 0) }} km</strong></div>
                                <div><span style="color:var(--muted)">Fuel at Pickup</span><br><strong>{{ $booking->rental->pickup_fuel ?? '—' }}%</strong></div>
                            </div>
                            @endif
                            <a href="{{ route('admin.rentals.index') }}" class="btn btn-primary" style="width:100%">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:6px"><path d="M3 13l1.5-4.5A3 3 0 0 1 7.35 6h9.3a3 3 0 0 1 2.85 2.5L21 13"/><path d="M5 16h14"/><circle cx="7.5" cy="15.5" r="1.5"/><circle cx="16.5" cy="15.5" r="1.5"/></svg>
                                View in Ongoing Rentals
                            </a>
                            @if(!$booking->inspections()->where('type','return')->exists())
                            <a href="{{ route('admin.bookings.inspection.create', $booking) }}?type=return" class="btn btn-ghost" style="width:100%">Record Post-Return Inspection</a>
                            @else
                            <a href="{{ route('admin.returns.index') }}" class="btn btn-success" style="width:100%">Process Official Return →</a>
                            @endif
                        </div>
                    </div>
                @endif

                
                <hr style="border-color:var(--line)">
                <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Cancellation Reason</label>
                        <input type="text" name="cancellation_reason" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm" style="width:100%">Cancel Booking</button>
                </form>
            </div>
        </div>
        @endif

        @if(!in_array($booking->status, ['cancelled', 'rejected']))
        <div class="card" style="margin-bottom:16px">
            <div class="card-header">
                <span class="card-title">Record Cash Payment</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.bookings.record-payment', $booking) }}">
                    @csrf
                    <div class="form-group" style="margin-bottom:12px">
                        <label class="form-label">Cash Amount Received <span style="color:var(--red)">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="e.g. 500.00" required>
                        <span style="font-size:0.75rem; color:var(--text-dim); display:block; margin-top:4px">
                            Remaining Balance: <strong>{{ $booking->balance_amount > 0 ? '₱' . number_format($booking->balance_amount, 2) : '₱0.00 (Fully Settled)' }}</strong>
                        </span>
                    </div>
                    <div class="form-group" style="margin-bottom:14px">
                        <label class="form-label">Payment Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Refueling fee, cleaning fee, damage charge…"></textarea>
                    </div>
                    <button type="submit" class="btn btn-ghost btn-sm" style="width:100%">+ Record Cash Payment</button>
                </form>
            </div>
        </div>
        @endif

        @if($booking->inspections->count() > 0)
        <div class="card" style="margin-bottom:16px">
            <div class="card-header"><span class="card-title">Inspections</span></div>
            <div class="card-body" style="padding:0">
                @foreach($booking->inspections as $ins)
                <div style="padding:16px 22px; {{ !$loop->last ? 'border-bottom:1px solid var(--line)' : '' }}">
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                        <span class="badge {{ $ins->type === 'pickup' ? 'bg_' : 'bo' }}">{{ ucfirst($ins->type) }}</span>
                        <span style="font-size:.75rem;color:var(--muted)">{{ $ins->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div style="font-size:.85rem;margin-bottom:4px"><strong>Odometer:</strong> {{ number_format($ins->odometer_reading) }} km</div>
                    <div style="font-size:.85rem;margin-bottom:4px"><strong>Fuel:</strong> {{ $ins->fuel_level }}</div>
                    <div style="font-size:.85rem;color:var(--muted)">{{ $ins->notes }}</div>
                    @if($ins->images_paths)
                    <div style="display:flex;gap:4px;margin-top:10px;overflow-x:auto;padding-bottom:4px">
                        @foreach($ins->images_paths as $img)
                        <img src="{{ asset('storage/'.$img) }}" style="height:60px;width:60px;border-radius:4px;object-fit:cover;cursor:pointer" onclick="window.open(this.src)">
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
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
