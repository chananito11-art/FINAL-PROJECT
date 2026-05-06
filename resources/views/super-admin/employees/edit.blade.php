@extends('layouts.app')
@section('title','Edit Employee — ' . $user->first_name)
@section('page-title','Edit Employee')
@section('content')

<div style="max-width:560px">
    <div style="margin-bottom:20px">
        <a href="{{ route('super-admin.employees.index') }}" class="btn btn-ghost btn-sm">← Back to Employees</a>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">{{ $user->first_name }} {{ $user->last_name }}</span>
            @if($user->status==='active') <span class="badge bg_">Active</span>
            @elseif($user->status==='inactive') <span class="badge bgy">Inactive</span>
            @else <span class="badge br">Suspended</span> @endif
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('super-admin.employees.update', $user) }}">
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
                            {{ ucwords(str_replace('_',' ',$role->name)) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%">Save Changes</button>
            </form>
        </div>
    </div>

    @if($user->id !== auth()->id())
    <div class="card" style="margin-top:16px;border-color:rgba(239,68,68,.2)">
        <div class="card-body">
            <div style="font-size:.9rem;font-weight:700;margin-bottom:6px;color:var(--red)">Danger Zone</div>
            <p style="font-size:.85rem;color:var(--muted);margin-bottom:14px">Deactivating will revoke this employee's access to the admin panel.</p>
            @if($user->status !== 'inactive')
            <form method="POST" action="{{ route('super-admin.employees.deactivate', $user) }}">
                @csrf @method('PUT')
                <button class="btn btn-danger" onclick="return confirm('Deactivate {{ $user->first_name }}? This will block their login.')">Deactivate Account</button>
            </form>
            @else
            <p style="font-size:.85rem;color:var(--red)">This account is already deactivated.</p>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
