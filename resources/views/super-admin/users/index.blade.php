@extends('layouts.app')
@section('title','User Management')
@section('page-title','User Management')
@section('content')
<div style="margin-bottom:16px">
    <form method="GET" action="{{ route('super-admin.users.index') }}" style="display:flex;gap:10px">
        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Search by name or email…" style="max-width:320px">
        <button type="submit" class="btn btn-ghost">Search</button>
        @if($search)<a href="{{ route('super-admin.users.index') }}" class="btn btn-ghost">Clear</a>@endif
    </form>
</div>
<div class="card">
    <div class="card-header"><span class="card-title">All Users</span><span style="font-size:.85rem;color:var(--text-dim)">{{ $users->total() }} total</span></div>
    <div class="tw">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($users as $user)
            <tr>
                <td style="font-weight:600">{{ $user->first_name }} {{ $user->last_name }}</td>
                <td style="font-size:.88rem;color:var(--muted)">{{ $user->email }}</td>
                <td style="font-size:.85rem;color:var(--muted)">{{ $user->phone ?? '—' }}</td>
                <td>
                    @php $role=$user->getRoleNames()->first()??'none'; $rc=['customer'=>'bgy','admin'=>'bo','super_admin'=>'bb','none'=>'br']; @endphp
                    <span class="badge {{ $rc[$role]??'bgy' }}">{{ str_replace('_',' ',ucfirst($role)) }}</span>
                </td>
                <td style="font-size:.82rem;color:var(--text-dim)">{{ $user->created_at->format('M d, Y') }}</td>
                <td>
                    <button onclick="openRole({{ $user->id }},'{{ $role }}')" class="btn btn-ghost btn-sm">Change Role</button>
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('super-admin.users.destroy',$user) }}" style="display:inline" onsubmit="return confirm('Delete this user?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-dim)">No users found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())<div style="padding:16px;border-top:1px solid rgba(255,255,255,.06)">{{ $users->links() }}</div>@endif
</div>

<div id="roleModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;align-items:center;justify-content:center">
    <div style="background:var(--dark2);border:1px solid var(--line);border-radius:18px;width:100%;max-width:380px;padding:28px">
        <h2 style="font-size:1.05rem;font-weight:800;margin-bottom:16px;color:var(--text)">Change Role</h2>
        <form method="POST" id="roleForm">@csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">New Role</label>
                <select name="role" id="roleSelect" class="form-control">
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
            <div style="display:flex;gap:10px;margin-top:8px">
                <button type="button" onclick="document.getElementById('roleModal').style.display='none'" class="btn btn-ghost" style="flex:1">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
function openRole(id,role){
    document.getElementById('roleForm').action='/super-admin/users/'+id+'/role';
    document.getElementById('roleSelect').value=role;
    document.getElementById('roleModal').style.display='flex';
}
</script>
@endpush
