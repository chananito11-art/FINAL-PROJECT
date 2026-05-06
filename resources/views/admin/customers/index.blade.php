@extends('layouts.app')
@section('title','Customer Management')
@section('page-title','Customers')
@section('content')

<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <span class="card-title">All Customers</span>
        <span style="font-size:.82rem;color:var(--muted)">{{ $query->total() }} total</span>
    </div>
    <div class="card-body" style="padding-bottom:0">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email…" class="form-control" style="flex:1;min-width:200px">
            <select name="status" class="form-control" style="width:150px">
                <option value="">All Status</option>
                <option value="active"    {{ request('status')==='active'    ? 'selected' : '' }}>Active</option>
                <option value="suspended" {{ request('status')==='suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-ghost">Reset</a>
        </form>
    </div>
    <div class="tw" style="margin-top:12px">
        <table>
            <thead><tr>
                <th>#</th><th>Name</th><th>Email</th><th>Phone</th>
                <th>Bookings</th><th>Last Login</th><th>Status</th><th>Actions</th>
            </tr></thead>
            <tbody>
            @forelse($query as $customer)
            <tr>
                <td style="color:var(--muted);font-size:.82rem">#{{ $customer->id }}</td>
                <td style="font-weight:600">{{ $customer->first_name }} {{ $customer->last_name }}</td>
                <td style="color:var(--muted);font-size:.88rem">{{ $customer->email }}</td>
                <td style="font-size:.88rem">{{ $customer->phone ?? '—' }}</td>
                <td><span class="badge bgy">{{ $customer->bookings_count }}</span></td>
                <td style="font-size:.82rem;color:var(--muted)">
                    {{ $customer->last_login_at ? $customer->last_login_at->diffForHumans() : 'Never' }}
                </td>
                <td>
                    @if($customer->status === 'active')
                        <span class="badge bg_">Active</span>
                    @else
                        <span class="badge br">Suspended</span>
                    @endif
                </td>
                <td>
                    <div class="flex" style="gap:6px">
                        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-ghost btn-sm">View</a>
                        @if($customer->status === 'active')
                        <form method="POST" action="{{ route('admin.customers.suspend', $customer) }}">
                            @csrf @method('PUT')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Suspend this customer?')">Suspend</button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('admin.customers.activate', $customer) }}">
                            @csrf @method('PUT')
                            <button class="btn btn-success btn-sm">Activate</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:32px">No customers found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($query->hasPages())
    <div style="padding:16px 22px;border-top:1px solid var(--line)">{{ $query->links() }}</div>
    @endif
</div>
@endsection
