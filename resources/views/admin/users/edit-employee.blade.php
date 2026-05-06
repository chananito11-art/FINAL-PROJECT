@extends('layouts.app')
@section('title','Edit Employee — ' . $user->first_name)
@section('page-title','Edit Employee')
@section('content')
<div style="max-width:560px">
    <div style="margin-bottom:20px">
        <a href="{{ route('admin.users.index') }}?tab=employees" class="btn btn-ghost btn-sm">← Back to User Management</a>
    </div>

    <div class="card" style="margin-bottom:16px">
        <div class="card-header">
            <span class="card-title">{{ $user->first_name }} {{ $user->last_name }}</span>
            @if($user->status === 'active') <span class="badge bg_">Active</span>
            @elseif($user->status === 'inactive') <span class="badge bgy">Inactive</span>
            @else <span class="badge br">Suspended</span> @endif
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.employees.update', $user) }}">
                @csrf @method('PUT')
                @if($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
                @endif

                <div class="g2">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control" required>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                            {{ ucwords(str_replace('_', ' ', $role->name)) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div style="border-top:1px solid var(--line);padding-top:16px;margin-top:4px">
                    <p style="font-size:.82rem;color:var(--muted);margin-bottom:12px">Leave password fields blank to keep the current password.</p>
                    <div class="g2">
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" minlength="8">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:4px">Save Changes</button>
            </form>
        </div>
    </div>

    @if($user->id !== auth()->id())
    <div class="card" style="border-color:rgba(239,68,68,.2)">
        <div class="card-body">
            <div style="font-size:.9rem;font-weight:700;color:var(--red);margin-bottom:6px">Danger Zone</div>
            <p style="font-size:.84rem;color:var(--muted);margin-bottom:14px">
                Deactivating will prevent this employee from logging in. You can reactivate them later.
            </p>
            @if($user->status !== 'inactive')
            <form method="POST" action="{{ route('admin.users.employees.deactivate', $user) }}">
                @csrf @method('PUT')
                <button class="btn btn-danger" onclick="return confirm('Deactivate {{ addslashes($user->first_name) }}? They will lose access.')">
                    Deactivate Account
                </button>
            </form>
            @else
            <form method="POST" action="{{ route('admin.users.employees.reactivate', $user) }}">
                @csrf @method('PUT')
                <button class="btn btn-success">Reactivate Account</button>
            </form>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
