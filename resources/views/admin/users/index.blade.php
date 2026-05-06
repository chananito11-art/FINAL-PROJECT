@extends('layouts.app')
@section('title','User Management')
@section('page-title','User Management')
@push('styles')
<style>
.tab-bar{display:flex;gap:4px;background:var(--card-bg);border:1px solid var(--line);border-radius:12px;padding:4px;margin-bottom:20px;width:fit-content}
.tab-btn{padding:8px 20px;border-radius:9px;font-size:.88rem;font-weight:700;color:var(--muted);text-decoration:none;transition:all .15s;display:flex;align-items:center;gap:8px}
.tab-btn.active{background:rgba(255,107,0,.15);color:var(--orange-l)}
.tab-btn:hover:not(.active){background:var(--hover-bg);color:var(--text)}
.count-pill{font-size:.72rem;padding:2px 8px;border-radius:20px;background:rgba(255,255,255,.08)}
.tab-btn.active .count-pill{background:rgba(255,107,0,.2);color:var(--orange-l)}
.section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px}
</style>
@endpush
@section('content')

{{-- Tabs --}}
<div class="tab-bar">
    <a href="{{ route('admin.users.index') }}?tab=customers{{ request('search') ? '&search='.request('search') : '' }}"
       class="tab-btn {{ $tab === 'customers' ? 'active' : '' }}">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        Customers <span class="count-pill">{{ $customers->total() }}</span>
    </a>
    <a href="{{ route('admin.users.index') }}?tab=employees{{ request('search') ? '&search='.request('search') : '' }}"
       class="tab-btn {{ $tab === 'employees' ? 'active' : '' }}">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
        Employees <span class="count-pill">{{ $employees->total() }}</span>
    </a>
</div>

{{-- Search Bar --}}
<div class="card" style="margin-bottom:16px">
    <div class="card-body" style="padding-top:14px;padding-bottom:14px">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email…" class="form-control" style="flex:1;min-width:200px">
            @if($tab === 'customers')
            <select name="status" class="form-control" style="width:150px">
                <option value="">All Status</option>
                <option value="active"    {{ request('status')==='active'    ? 'selected' : '' }}>Active</option>
                <option value="suspended" {{ request('status')==='suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
            @endif
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="{{ route('admin.users.index') }}?tab={{ $tab }}" class="btn btn-ghost">Reset</a>
            @if($tab === 'employees')
            <a href="{{ route('admin.users.employees.create') }}" class="btn btn-primary ml-auto" style="margin-left:auto">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Employee
            </a>
            @endif
        </form>
    </div>
</div>

{{-- ═══════════════════ CUSTOMERS TABLE ═══════════════════ --}}
@if($tab === 'customers')
<div class="card">
    <div class="card-header">
        <span class="card-title">Customer Accounts</span>
        <span style="font-size:.82rem;color:var(--muted)">{{ $customers->total() }} total</span>
    </div>
    <div class="tw">
        <table>
            <thead><tr>
                <th>#</th><th>Name</th><th>Email</th><th>Phone</th>
                <th>Bookings</th><th>Last Login</th><th>Status</th><th>Actions</th>
            </tr></thead>
            <tbody>
            @forelse($customers as $c)
            <tr>
                <td style="color:var(--muted);font-size:.82rem">#{{ $c->id }}</td>
                <td style="font-weight:600">{{ $c->first_name }} {{ $c->last_name }}</td>
                <td style="color:var(--muted);font-size:.87rem">{{ $c->email }}</td>
                <td style="font-size:.87rem">{{ $c->phone ?? '—' }}</td>
                <td><span class="badge bgy">{{ $c->bookings_count }}</span></td>
                <td style="font-size:.82rem;color:var(--muted)">{{ $c->last_login_at ? $c->last_login_at->diffForHumans() : 'Never' }}</td>
                <td>
                    @if($c->status === 'active') <span class="badge bg_">Active</span>
                    @else <span class="badge br">Suspended</span> @endif
                </td>
                <td>
                    <div class="flex" style="gap:6px">
                        <a href="{{ route('admin.users.customers.show', $c) }}" class="btn btn-ghost btn-sm">View</a>
                        @if($c->status === 'active')
                        <form method="POST" action="{{ route('admin.users.customers.suspend', $c) }}">
                            @csrf @method('PUT')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Suspend {{ addslashes($c->first_name) }}?')">Suspend</button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('admin.users.customers.activate', $c) }}">
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
    @if($customers->hasPages())
    <div style="padding:16px 22px;border-top:1px solid var(--line)">{{ $customers->links() }}</div>
    @endif
</div>

{{-- ═══════════════════ EMPLOYEES TABLE ═══════════════════ --}}
@else
<div class="card">
    <div class="card-header">
        <span class="card-title">Employee Accounts</span>
        <span style="font-size:.82rem;color:var(--muted)">{{ $employees->total() }} total</span>
    </div>
    <div class="tw">
        <table>
            <thead><tr>
                <th>#</th><th>Name</th><th>Email</th><th>Role</th>
                <th>Status</th><th>Last Login</th><th>Created By</th><th>Actions</th>
            </tr></thead>
            <tbody>
            @forelse($employees as $emp)
            <tr>
                <td style="color:var(--muted);font-size:.82rem">#{{ $emp->id }}</td>
                <td style="font-weight:600">{{ $emp->first_name }} {{ $emp->last_name }}
                    @if($emp->id === auth()->id()) <span style="font-size:.72rem;color:var(--orange-l)">(you)</span> @endif
                </td>
                <td style="color:var(--muted);font-size:.87rem">{{ $emp->email }}</td>
                <td>
                    @if($emp->hasRole('super_admin')) <span class="badge bo">Super Admin</span>
                    @else <span class="badge bb">Admin</span> @endif
                </td>
                <td>
                    @if($emp->status === 'active') <span class="badge bg_">Active</span>
                    @elseif($emp->status === 'inactive') <span class="badge bgy">Inactive</span>
                    @else <span class="badge br">Suspended</span> @endif
                </td>
                <td style="font-size:.82rem;color:var(--muted)">{{ $emp->last_login_at ? $emp->last_login_at->diffForHumans() : 'Never' }}</td>
                <td style="font-size:.85rem">{{ $emp->createdBy?->first_name ?? '—' }}</td>
                <td>
                    <div class="flex" style="gap:6px">
                        <a href="{{ route('admin.users.employees.edit', $emp) }}" class="btn btn-ghost btn-sm">Edit</a>
                        @if($emp->id !== auth()->id())
                            @if($emp->status !== 'inactive')
                            <form method="POST" action="{{ route('admin.users.employees.deactivate', $emp) }}">
                                @csrf @method('PUT')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Deactivate {{ addslashes($emp->first_name) }}?')">Deactivate</button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('admin.users.employees.reactivate', $emp) }}">
                                @csrf @method('PUT')
                                <button class="btn btn-success btn-sm">Reactivate</button>
                            </form>
                            @endif
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:32px">No employees found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($employees->hasPages())
    <div style="padding:16px 22px;border-top:1px solid var(--line)">{{ $employees->links() }}</div>
    @endif
</div>
@endif

@endsection
