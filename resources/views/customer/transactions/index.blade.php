@extends('layouts.customer')

@section('title', 'Transaction History')
@section('title_display', 'Transaction History')

@push('styles')
<style>
    .trans-wrap { max-width: 1000px; margin: 40px auto; padding: 0 24px 80px; }
    .card { background: var(--card-bg); border: 1px solid var(--line); border-radius: 20px; overflow: hidden; }
    
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 16px 24px; font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); border-bottom: 1px solid var(--line); background: rgba(255,255,255,.02); }
    td { padding: 20px 24px; border-bottom: 1px solid var(--line); font-size: .95rem; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: var(--hover-bg); }

    .vehicle-info { display: flex; align-items: center; gap: 12px; }
    .v-img { width: 48px; height: 36px; border-radius: 6px; object-fit: cover; background: var(--dark2); }
    .v-name { font-weight: 700; font-size: .9rem; }
    
    .amount { font-weight: 800; font-family: monospace; font-size: 1rem; }
    .date { color: var(--muted); font-size: .88rem; }
    
    .status { font-size: .72rem; font-weight: 800; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; }
    .s-verified { background: rgba(34,197,94,.12); color: #4ade80; }
    .s-pending { background: rgba(245,158,11,.12); color: #fbbf24; }
    .s-rejected { background: rgba(239,68,68,.12); color: #f87171; }
    .s-refunded { background: rgba(59,130,246,.12); color: #60a5fa; }
    
    .empty { text-align: center; padding: 80px 20px; color: var(--muted); }
</style>
@endpush

@section('content')
<div class="trans-wrap">
    <div class="card">
        <div style="overflow-x: auto">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Vehicle / Booking</th>
                        <th>Reference</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                    <tr>
                        <td>
                            <div class="date">{{ $t->created_at->format('M d, Y') }}</div>
                            <div style="font-size: .75rem; opacity: .6">{{ $t->created_at->format('h:i A') }}</div>
                        </td>
                        <td>
                            <div class="vehicle-info">
                                <img src="{{ $t->booking->vehicle->image_url }}" class="v-img">
                                <div>
                                    <div class="v-name">{{ $t->booking->vehicle->name }}</div>
                                    <div style="font-size: .78rem; color: var(--muted)">Booking #{{ $t->booking_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-family: monospace; font-size: .85rem">{{ $t->gcash_transaction_reference_number }}</div>
                            <div style="font-size: .7rem; color: var(--muted)">{{ $t->gcash_account_name }}</div>
                        </td>
                        <td><div class="amount">₱{{ number_format($t->amount_submitted, 2) }}</div></td>
                        <td>
                            <span class="status s-{{ $t->status }}">{{ $t->status }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty">
                                <p>No transaction history found.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
