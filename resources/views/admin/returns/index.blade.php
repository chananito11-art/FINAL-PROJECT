@extends('layouts.app')
@section('title','Return Processing')
@section('page-title','Return Processing')
@section('content')
<div class="card">
    <div class="card-header"><span class="card-title">Ongoing Rentals</span><span style="font-size:.85rem;color:rgba(240,242,255,.45)">{{ $bookings->total() }} active</span></div>
    <div class="tw">
        <table>
            <thead><tr><th>Booking #</th><th>Customer</th><th>Vehicle</th><th>Return Date</th><th>Amount</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($bookings as $b)
            <tr>
                <td style="color:rgba(240,242,255,.45)">#{{ $b->id }}</td>
                <td><div style="font-weight:600">{{ $b->first_name }} {{ $b->last_name }}</div><div style="font-size:.8rem;color:rgba(240,242,255,.45)">{{ $b->phone }}</div></td>
                <td>{{ $b->vehicle?->name }}</td>
                <td>
                    @php $overdue=$b->return_date->isPast(); @endphp
                    <span style="color:{{ $overdue?'#f87171':'#f0f2ff' }}">{{ $b->return_date->format('M d, Y') }}</span>
                    @if($overdue)<span class="badge br" style="margin-left:6px">Overdue</span>@endif
                </td>
                <td style="color:#ff8c3a;font-weight:700">₱{{ number_format($b->total_amount,0) }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.returns.process',$b) }}" onsubmit="return confirm('Mark this rental as returned?')">@csrf
                        <button type="submit" class="btn btn-success btn-sm">✓ Process Return</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;padding:40px;color:rgba(240,242,255,.4)">No ongoing rentals.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($bookings->hasPages())<div style="padding:16px;border-top:1px solid rgba(255,255,255,.06)">{{ $bookings->links() }}</div>@endif
</div>
@endsection
