@extends('layouts.app')
@section('title','Payments')
@section('page-title','Payments')
@push('styles')
<style>
.tab-bar{display:flex;gap:4px;background:var(--card-bg);border:1px solid var(--line);border-radius:12px;padding:4px;margin-bottom:20px;width:fit-content}
.tab-btn{padding:7px 18px;border-radius:9px;font-size:.85rem;font-weight:600;color:var(--muted);text-decoration:none;transition:all .15s;display:flex;align-items:center;gap:6px}
.tab-btn.active{background:rgba(255,107,0,.15);color:var(--orange-l)}
.tab-btn:hover:not(.active){background:var(--hover-bg);color:var(--text)}
.count-chip{font-size:.72rem;background:var(--badge-y);padding:2px 7px;border-radius:20px}
.tab-btn.active .count-chip{background:rgba(255,107,0,.2);color:var(--orange-l)}

/* Progress bar */
.pay-bar-wrap{height:5px;border-radius:4px;background:var(--ghost-bg);overflow:hidden;margin-top:5px;min-width:80px}
.pay-bar-fill{height:100%;border-radius:4px;transition:width .3s}

/* Row tinting */
tr.row-full td{background:rgba(34,197,94,.03)}
tr.row-partial td{background:rgba(251,191,36,.04)}
tr.row-unpaid td{background:rgba(239,68,68,.04)}

/* Stat strip */
.stat-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
.stat-box{background:var(--card-bg);border:1px solid var(--line);border-radius:14px;padding:16px 20px}
.stat-box .s-val{font-size:1.4rem;font-weight:900;letter-spacing:-.03em}
.stat-box .s-lbl{font-size:.75rem;color:var(--muted);margin-top:2px}
@media(max-width:768px){.stat-strip{grid-template-columns:1fr 1fr}}
</style>
@endpush
@section('content')

{{-- Tabs --}}
<div class="tab-bar">
    @php $base = route('admin.payments.index') @endphp
    <a href="{{ $base }}?tab=pending"  class="tab-btn {{ $tab==='pending'  ? 'active':'' }}">Pending  <span class="count-chip">{{ $counts['pending']  }}</span></a>
    <a href="{{ $base }}?tab=verified" class="tab-btn {{ $tab==='verified' ? 'active':'' }}">Verified <span class="count-chip">{{ $counts['verified'] }}</span></a>
    <a href="{{ $base }}?tab=rejected" class="tab-btn {{ $tab==='rejected' ? 'active':'' }}">Rejected <span class="count-chip">{{ $counts['rejected'] }}</span></a>
    <a href="{{ $base }}?tab=all"      class="tab-btn {{ $tab==='all'      ? 'active':'' }}">All      <span class="count-chip">{{ $counts['all']      }}</span></a>
</div>

{{-- Stats Strip --}}
<div class="stat-strip">
    <div class="stat-box">
        <div class="s-val" style="color:var(--orange-l)">{{ $counts['pending'] }}</div>
        <div class="s-lbl">Awaiting Verification</div>
    </div>
    <div class="stat-box">
        <div class="s-val" style="color:#4ade80">₱{{ number_format($stats['verified_today'], 0) }}</div>
        <div class="s-lbl">Verified Today</div>
    </div>
    <div class="stat-box">
        <div class="s-val" style="color:var(--text)">₱{{ number_format($stats['total_verified'], 0) }}</div>
        <div class="s-lbl">Total Verified (All Time)</div>
    </div>
    <div class="stat-box">
        <div class="s-val" style="color:#f87171">{{ $stats['bookings_with_balance'] }}</div>
        <div class="s-lbl">Bookings with Outstanding Balance</div>
    </div>
</div>

{{-- Search & Filter --}}
<div class="card" style="margin-bottom:16px">
    <div class="card-body" style="padding-top:14px;padding-bottom:14px">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ref #, customer, booking #…" class="form-control" style="flex:1;min-width:220px">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" style="width:155px">
            <input type="date" name="date_to"   value="{{ request('date_to')   }}" class="form-control" style="width:155px">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="{{ $base }}?tab={{ $tab }}" class="btn btn-ghost">Reset</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="tw">
        <table>
            <thead><tr>
                <th>#</th>
                <th>Booking</th>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Payment</th>
                <th>Coverage</th>
                <th>Ref #</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr></thead>
            <tbody>
            @forelse($payments as $payment)
            @php
                $b        = $payment->booking;
                $total    = $b?->total_amount ?? 0;
                $paid     = $b?->paid_amount  ?? 0;
                $balance  = max(0, $total - $paid);
                $pct      = $total > 0 ? min(100, round(($paid / $total) * 100)) : 0;
                $rowClass = $pct >= 100 ? 'row-full' : ($pct > 0 ? 'row-partial' : 'row-unpaid');
                $barColor = $pct >= 100 ? '#4ade80' : ($pct > 0 ? '#fbbf24' : '#f87171');
                // Customer name — works for both online and walk-in
                $custName = $b?->user
                    ? ($b->user->first_name . ' ' . $b->user->last_name)
                    : ($b?->first_name . ' ' . $b?->last_name);
            @endphp
            <tr class="{{ $rowClass }}">
                <td style="color:var(--muted);font-size:.82rem">#{{ $payment->id }}</td>
                <td>
                    <a href="{{ route('admin.bookings.show', $b) }}"
                       style="font-size:.85rem;font-weight:600;color:var(--orange-l)">
                        Bk#{{ $b?->id }}
                    </a>
                    @if(!$b?->user_id)
                    <span style="font-size:.7rem;color:var(--muted);display:block">Walk-in</span>
                    @endif
                </td>
                <td style="font-weight:600;font-size:.88rem">{{ $custName ?: '—' }}</td>
                <td style="font-size:.85rem;color:var(--muted)">{{ $b?->vehicle?->name ?? '—' }}</td>
                <td>
                    <div style="font-weight:700;color:var(--orange-l)">₱{{ number_format($payment->amount, 0) }}</div>
                    @if($total > 0)
                    <div style="font-size:.75rem;color:var(--muted)">of ₱{{ number_format($total, 0) }}</div>
                    @endif
                </td>
                <td style="min-width:100px">
                    @if($total > 0)
                    <div style="font-size:.78rem;font-weight:700;color:{{ $barColor }}">{{ $pct }}%
                        @if($balance > 0)
                        <span style="font-weight:400;color:var(--muted)"> · ₱{{ number_format($balance, 0) }} left</span>
                        @endif
                    </div>
                    <div class="pay-bar-wrap">
                        <div class="pay-bar-fill" style="width:{{ $pct }}%;background:{{ $barColor }}"></div>
                    </div>
                    @else
                    <span style="color:var(--muted);font-size:.82rem">—</span>
                    @endif
                </td>
                <td style="font-size:.78rem;font-family:monospace;color:var(--muted)">
                    {{ $payment->gcash_transaction_reference_number ?? ($payment->reference_code ?? '—') }}
                </td>
                <td style="font-size:.82rem;color:var(--muted)">{{ $payment->created_at->format('M d, Y') }}</td>
                <td>
                    @if($payment->status==='verified')   <span class="badge bg_">Verified</span>
                    @elseif($payment->status==='rejected') <span class="badge br">Rejected</span>
                    @else                                   <span class="badge by">Pending</span>
                    @endif
                </td>
                <td>
                    <div class="flex" style="gap:6px">
                        <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-ghost btn-sm">View</a>
                        @if($payment->status==='pending')
                        <form method="POST" action="{{ route('admin.payments.verify', $payment) }}">
                            @csrf
                            <input type="hidden" name="amount_matched" value="1">
                            <button class="btn btn-success btn-sm">Verify</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="10" style="text-align:center;color:var(--muted);padding:36px">No payments found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
    <div style="padding:16px 22px;border-top:1px solid var(--line)">{{ $payments->links() }}</div>
    @endif
</div>
@endsection
