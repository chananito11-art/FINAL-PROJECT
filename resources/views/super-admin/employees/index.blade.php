@extends('layouts.app')
@section('title','Employee Management')
@section('page-title','Employees')
@section('content')

<div class="flex" style="margin-bottom:20px">
    <h2 style="font-size:1rem;color:var(--muted)">{{ $employees->total() }} employee(s)</h2>
    <a href="{{ route('super-admin.employees.create') }}" class="btn btn-primary ml-auto">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Employee
    </a>
</div>

<div class="card">
    <div class="tw">
        <table>
            <thead><tr>
                <th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th>Created By</th><th>Actions</th>
            </tr></thead>
            <tbody>
            @forelse($employees as $emp)
            <tr>
                <td style="color:var(--muted);font-size:.82rem">#{{ $emp->id }}</td>
                <td style="font-weight:600">{{ $emp->first_name }} {{ $emp->last_name }}</td>
                <td style="font-size:.88rem;color:var(--muted)">{{ $emp->email }}</td>
                <td>
                    @if($emp->hasRole('super_admin'))
                        <span class="badge bo">Super Admin</span>
                    @else
                        <span class="badge bb">Admin</span>
                    @endif
                </td>
                <td>
                    @if($emp->status === 'active')
                        <span class="badge bg_">Active</span>
                    @elseif($emp->status === 'inactive')
                        <span class="badge bgy">Inactive</span>
                    @else
                        <span class="badge br">Suspended</span>
                    @endif
                </td>
                <td style="font-size:.82rem;color:var(--muted)">
                    {{ $emp->last_login_at ? $emp->last_login_at->diffForHumans() : 'Never' }}
                </td>
                <td style="font-size:.85rem">{{ $emp->createdBy?->first_name ?? '—' }}</td>
                <td>
                    <div class="flex" style="gap:6px">
                        <a href="{{ route('super-admin.employees.edit', $emp) }}" class="btn btn-ghost btn-sm">Edit</a>
                        @if($emp->status !== 'inactive' && $emp->id !== auth()->id())
                        <form method="POST" action="{{ route('super-admin.employees.deactivate', $emp) }}">
                            @csrf @method('PUT')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Deactivate {{ $emp->first_name }}? They will lose access.')">Deactivate</button>
                        </form>
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
@endsection
