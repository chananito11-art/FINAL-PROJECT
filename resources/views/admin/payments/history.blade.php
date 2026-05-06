@extends('layouts.app')
@section('title','Payment History')
@section('page-title','Payment History')
@section('content')

<div class="card" style="margin-bottom:16px">
    <div class="card-body" style="padding-top:16px;padding-bottom:16px">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ref #, customer, GCash #…" class="form-control" style="flex:1;min-width:220px">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" style="width:160px">
            <input type="date" name="date_to"   value="{{ request('date_to') }}"   class="form-control" style="width:160px">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="{{ route('admin.payments.history') }}" class="btn btn-ghost">Reset</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Verified &amp; Rejected Payments</span>
        <span style="font-size:.82rem;color:var(--muted)">{{ $payments->total() }} records</span>
    </div>
    <div class="tw">
        <table>
            <thead><tr>
                <th>#</th><th>Booking</th><th>Customer</th><th>Vehicle</th>
                <th>Amount</th><th>GCash Ref #</th><th>Status</th><th>Verified At</th><th></th>
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
                <td style="font-size:.82rem;font-family:monospace">{{ $payment->gcash_transaction_reference_number ?? '—' }}</td>
                <td>
                    @if($payment->status==='verified') <span class="badge bg_">Verified</span>
                    @else <span class="badge br">Rejected</span> @endif
                </td>
                <td style="font-size:.82rem;color:var(--muted)">{{ $payment->verified_at?->format('M d, Y') }}</td>
                <td><a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-ghost btn-sm">View</a></td>
            </tr>
            @empty
            <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:32px">No records found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
    <div style="padding:16px 22px;border-top:1px solid var(--line)">{{ $payments->links() }}</div>
    @endif
</div>
@endsection
