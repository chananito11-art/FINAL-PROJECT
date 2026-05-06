@extends('layouts.app')
@section('title','Customer — ' . $user->first_name . ' ' . $user->last_name)
@section('page-title','Customer Profile')
@section('content')

<div style="margin-bottom:16px">
    <a href="{{ route('admin.users.index') }}?tab=customers" class="btn btn-ghost btn-sm">← Back to User Management</a>
</div>

<div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start">
    {{-- Profile card --}}
    <div class="card">
        <div class="card-body" style="text-align:center">
            <div style="width:68px;height:68px;border-radius:50%;background:linear-gradient(135deg,var(--orange-l),var(--orange));display:grid;place-items:center;margin:0 auto 14px;font-size:1.7rem;font-weight:900">
                {{ strtoupper(substr($user->first_name, 0, 1)) }}
            </div>
            <div style="font-size:1.1rem;font-weight:800;margin-bottom:4px">{{ $user->first_name }} {{ $user->last_name }}</div>
            <div style="font-size:.85rem;color:var(--muted);margin-bottom:14px">{{ $user->email }}</div>
            @if($user->status === 'active') <span class="badge bg_">Active</span>
            @else <span class="badge br">Suspended</span> @endif
        </div>
        <div style="border-top:1px solid var(--line);padding:16px 22px">
            <div style="display:flex;flex-direction:column;gap:9px;font-size:.87rem">
                <div><span style="color:var(--muted)">Phone: </span>{{ $user->phone ?? '—' }}</div>
                <div><span style="color:var(--muted)">Joined: </span>{{ $user->created_at->format('M d, Y') }}</div>
                <div><span style="color:var(--muted)">Last Login: </span>{{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : 'Never' }}</div>
                <div><span style="color:var(--muted)">Total Bookings: </span><strong>{{ $bookings->total() }}</strong></div>
            </div>
        </div>
        <div style="padding:0 22px 18px;display:flex;flex-direction:column;gap:8px">
            @if($user->status === 'active')
            <form method="POST" action="{{ route('admin.users.customers.suspend', $user) }}">
                @csrf @method('PUT')
                <button class="btn btn-danger" style="width:100%" onclick="return confirm('Suspend {{ addslashes($user->first_name) }}?')">Suspend Account</button>
            </form>
            @else
            <form method="POST" action="{{ route('admin.users.customers.activate', $user) }}">
                @csrf @method('PUT')
                <button class="btn btn-success" style="width:100%">Activate Account</button>
            </form>
            @endif
        </div>
    </div>

    {{-- Booking history --}}
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
                @php $badges=['pending_payment'=>'by','awaiting_verification'=>'bb','confirmed'=>'bg_','rejected'=>'br','ongoing'=>'bo','completed'=>'bgy','cancelled'=>'br']; @endphp
                <tr>
                    <td style="color:var(--muted);font-size:.82rem">#{{ $booking->id }}</td>
                    <td style="font-weight:600;font-size:.9rem">{{ $booking->vehicle?->name ?? '—' }}</td>
                    <td style="font-size:.85rem">{{ $booking->pickup_date?->format('M d, Y') }}</td>
                    <td style="font-size:.85rem">{{ $booking->return_date?->format('M d, Y') }}</td>
                    <td style="color:var(--orange-l);font-weight:700">₱{{ number_format($booking->total_amount,0) }}</td>
                    <td><span class="badge {{ $badges[$booking->status]??'bgy' }}">{{ ucwords(str_replace('_',' ',$booking->status)) }}</span></td>
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
