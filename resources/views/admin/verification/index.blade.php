@extends('layouts.app')
@section('title', 'Customer Verification')
@section('page-title', 'Pending Verifications')

@section('content')
<div class="card">
    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Loyalty Points</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingUsers as $user)
                <tr>
                    <td>
                        <div style="font-weight:700">{{ $user->name }}</div>
                        <div style="font-size:.75rem;color:var(--muted)">Joined {{ $user->created_at->format('M Y') }}</div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>{{ number_format($user->loyalty_points) }} pts</td>
                    <td><span class="badge bo">Pending</span></td>
                    <td>
                        <a href="{{ route('admin.verification.show', $user) }}" class="btn btn-primary btn-sm">Review Documents</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;padding:48px;color:var(--muted)">All caught up! No pending verifications.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pendingUsers->hasPages())<div style="padding:12px 22px;border-top:1px solid var(--line)">{{ $pendingUsers->links() }}</div>@endif
</div>
@endsection
