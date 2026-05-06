@extends('layouts.app')
@section('title','Add Employee')
@section('page-title','Add Employee')
@section('content')

<div style="max-width:560px">
    <div style="margin-bottom:20px">
        <a href="{{ route('super-admin.employees.index') }}" class="btn btn-ghost btn-sm">← Back to Employees</a>
    </div>

    <div class="card">
        <div class="card-header"><span class="card-title">New Employee Account</span></div>
        <div class="card-body">
            <form method="POST" action="{{ route('super-admin.employees.store') }}">
                @csrf
                @if($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
                @endif

                <div class="g2">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control" required>
                        <option value="">— Select Role —</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ old('role')===$role->name ? 'selected' : '' }}>
                            {{ ucwords(str_replace('_',' ',$role->name)) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="g2">
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%">Create Employee Account</button>
            </form>
        </div>
    </div>
</div>
@endsection
