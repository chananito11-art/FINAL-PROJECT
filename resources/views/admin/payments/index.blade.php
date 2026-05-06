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
</style>
@endpush
@section('content')

{{-- Tabs --}}
<div class="tab-bar">
    @php $base=route('admin.payments.index') @endphp
    <a href="{{ $base }}?tab=pending"   class="tab-btn {{ $tab==='pending'   ? 'active':'' }}">Pending   <span class="count-chip">{{ $counts['pending']  }}</span></a>
    <a href="{{ $base }}?tab=verified"  class="tab-btn {{ $tab==='verified'  ? 'active':'' }}">Verified  <span class="count-chip">{{ $counts['verified'] }}</span></a>
    <a href="{{ $base }}?tab=rejected"  class="tab-btn {{ $tab==='rejected'  ? 'active':'' }}">Rejected  <span class="count-chip">{{ $counts['rejected'] }}</span></a>
    <a href="{{ $base }}?tab=all"       class="tab-btn {{ $tab==='all'       ? 'active':'' }}">All       <span class="count-chip">{{ $counts['all']      }}</span></a>
</div>

{{-- Search & Filter --}}
<div class="card" style="margin-bottom:16px">
    <div class="card-body" style="padding-top:16px;padding-bottom:16px">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ref #, customer, booking #…" class="form-control" style="flex:1;min-width:220px">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" style="width:160px">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" style="width:160px">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="{{ $base }}?tab={{ $tab }}" class="btn btn-ghost">Reset</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="tw">
        <table>
            <thead><tr>
                <th>#</th><th>Booking</th><th>Customer</th><th>Vehicle</th><th>Amount</th>
                <th>GCash Ref #</th><th>Submitted</th><th>Status</th><th>Actions</th>
            </tr></thead>
            <tbody>
            @forelse($payments as $payment)
            @php $b = $payment->booking; @endphp
            <tr>
                <td style="color:var(--muted);font-size:.82rem">#{{ $payment->id }}</td>
                <td style="font-size:.85rem">Bk#{{ $b?->id }}</td>
                <td style="font-weight:600;font-size:.9rem">{{ $b?->user?->first_name }} {{ $b?->user?->last_name }}</td>
                <td style="font-size:.88rem;color:var(--muted)">{{ $b?->vehicle?->name }}</td>
                <td style="font-weight:700;color:var(--orange-l)">₱{{ number_format($payment->amount,0) }}</td>
                <td style="font-size:.82rem;font-family:monospace">
                    {{ $payment->gcash_transaction_reference_number ?? '—' }}
                </td>
                <td style="font-size:.82rem;color:var(--muted)">{{ $payment->created_at->format('M d, Y') }}</td>
                <td>
                    @if($payment->status==='verified') <span class="badge bg_">Verified</span>
                    @elseif($payment->status==='rejected') <span class="badge br">Rejected</span>
                    @else <span class="badge by">Pending</span> @endif
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
            <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:32px">No payments found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
    <div style="padding:16px 22px;border-top:1px solid var(--line)">{{ $payments->links() }}</div>
    @endif
</div>
@endsection
