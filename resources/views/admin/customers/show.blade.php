@extends('layouts.app')
@section('title','Customer — ' . $user->first_name . ' ' . $user->last_name)
@section('page-title','Customer Profile')
@section('content')

<div style="margin-bottom:16px">
    <a href="{{ route('admin.customers.index') }}" class="btn btn-ghost btn-sm">← Back to Customers</a>
</div>

<div style="display:grid;grid-template-columns:320px 1fr;gap:20px;align-items:start">
    {{-- Profile Card --}}
    <div class="card">
        <div class="card-body" style="text-align:center">
            <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--orange-l),var(--orange));display:grid;place-items:center;margin:0 auto 16px;font-size:1.8rem;font-weight:900">
                {{ strtoupper(substr($user->first_name,0,1)) }}
            </div>
            <div style="font-size:1.15rem;font-weight:800;margin-bottom:4px">{{ $user->first_name }} {{ $user->last_name }}</div>
            <div style="font-size:.85rem;color:var(--muted);margin-bottom:16px">{{ $user->email }}</div>
            @if($user->status === 'active')
                <span class="badge bg_">Active</span>
            @else
                <span class="badge br">Suspended</span>
            @endif
        </div>
        <div style="border-top:1px solid var(--line);padding:18px 22px">
            <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:12px">Details</div>
            <div style="display:flex;flex-direction:column;gap:10px;font-size:.88rem">
                <div><span style="color:var(--muted)">Phone: </span>{{ $user->phone ?? '—' }}</div>
                <div><span style="color:var(--muted)">Joined: </span>{{ $user->created_at->format('M d, Y') }}</div>
                <div><span style="color:var(--muted)">Last Login: </span>{{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : 'Never' }}</div>
                <div><span style="color:var(--muted)">Total Bookings: </span><strong>{{ $bookings->total() }}</strong></div>
            </div>
        </div>
        <div style="padding:0 22px 18px;display:flex;flex-direction:column;gap:8px">
            @if($user->status === 'active')
            <form method="POST" action="{{ route('admin.customers.suspend', $user) }}">
                @csrf @method('PUT')
                <button class="btn btn-danger" style="width:100%" onclick="return confirm('Suspend {{ $user->first_name }}?')">Suspend Account</button>
            </form>
            @else
            <form method="POST" action="{{ route('admin.customers.activate', $user) }}">
                @csrf @method('PUT')
                <button class="btn btn-success" style="width:100%">Activate Account</button>
            </form>
            @endif
        </div>
    </div>

    {{-- Booking History --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Booking History</span>
            <span style="font-size:.82rem;color:var(--muted)">{{ $bookings->total() }} bookings</span>
        </div>
        <div class="tw">
            <table>
                <thead><tr>
                    <th>#</th><th>Vehicle</th><th>Pickup</th><th>Return</th><th>Amount</th><th>Status</th><th></th>
                </tr></thead>
                <tbody>
                @forelse($bookings as $booking)
                <tr>
                    <td style="color:var(--muted);font-size:.82rem">#{{ $booking->id }}</td>
                    <td style="font-weight:600">{{ $booking->vehicle?->name ?? '—' }}</td>
                    <td style="font-size:.85rem">{{ $booking->pickup_date?->format('M d, Y') }}</td>
                    <td style="font-size:.85rem">{{ $booking->return_date?->format('M d, Y') }}</td>
                    <td style="color:var(--orange-l);font-weight:700">₱{{ number_format($booking->total_amount,0) }}</td>
                    <td>
                        @php $badges=['pending_payment'=>'by','awaiting_verification'=>'bb','confirmed'=>'bg_','rejected'=>'br','ongoing'=>'bo','completed'=>'bgy','cancelled'=>'br']; @endphp
                        <span class="badge {{ $badges[$booking->status]??'bgy' }}">{{ ucwords(str_replace('_',' ',$booking->status)) }}</span>
                    </td>
                    <td><a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-ghost btn-sm">View</a></td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:28px">No bookings yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($bookings->hasPages())
        <div style="padding:16px 22px;border-top:1px solid var(--line)">{{ $bookings->links() }}</div>
        @endif
    </div>
</div>
@endsection
