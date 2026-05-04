@extends('layouts.app')
@section('title','Vehicles')
@section('page-title','Vehicle Management')
@section('content')
<div style="display:flex;justify-content:flex-end;margin-bottom:16px">
    <button onclick="document.getElementById('addModal').style.display='flex'" class="btn btn-primary">+ Add Vehicle</button>
</div>
<div class="card">
    <div class="card-header"><span class="card-title">All Vehicles</span><span style="font-size:.85rem;color:rgba(240,242,255,.45)">{{ $vehicles->total() }} total</span></div>
    <div class="tw">
        <table>
            <thead><tr><th>Vehicle</th><th>Type</th><th>Brand</th><th>Price/Day</th><th>Status</th><th>Plate</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($vehicles as $v)
            <tr>
                <td style="font-weight:600">{{ $v->name }}</td>
                <td><span class="badge bgy">{{ $v->type }}</span></td>
                <td style="color:rgba(240,242,255,.6)">{{ $v->brand }}</td>
                <td style="color:#ff8c3a;font-weight:700">₱{{ number_format($v->price_per_day,0) }}</td>
                <td>
                    @php $sc=['available'=>'bg_','rented'=>'bo','maintenance'=>'by','unavailable'=>'br']; @endphp
                    <span class="badge {{ $sc[$v->status]??'bgy' }}">{{ ucfirst($v->status) }}</span>
                </td>
                <td style="font-size:.85rem;color:rgba(240,242,255,.45)">{{ $v->plate_number ?? '—' }}</td>
                <td>
                    <button onclick='openEdit({{ $v->toJson() }})' class="btn btn-ghost btn-sm">Edit</button>
                    <form method="POST" action="{{ route('admin.vehicles.destroy',$v) }}" style="display:inline" onsubmit="return confirm('Delete this vehicle?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:40px;color:rgba(240,242,255,.4)">No vehicles yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($vehicles->hasPages())<div style="padding:16px;border-top:1px solid rgba(255,255,255,.06)">{{ $vehicles->links() }}</div>@endif
</div>

{{-- Add Modal --}}
<div id="addModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;align-items:center;justify-content:center;padding:20px">
    <div style="background:#0d1128;border:1px solid rgba(255,255,255,.1);border-radius:20px;width:100%;max-width:640px;max-height:90vh;overflow-y:auto;padding:28px">
        <div style="display:flex;justify-content:space-between;margin-bottom:20px">
            <h2 style="font-size:1.1rem;font-weight:800">Add Vehicle</h2>
            <button onclick="document.getElementById('addModal').style.display='none'" style="background:none;border:none;cursor:pointer;color:rgba(240,242,255,.5);font-size:1.2rem">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.vehicles.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="g2"><div class="form-group"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Brand</label><input type="text" name="brand" class="form-control"></div></div>
            <div class="g2"><div class="form-group"><label class="form-label">Model</label><input type="text" name="model" class="form-control"></div>
            <div class="form-group"><label class="form-label">Year</label><input type="number" name="year" class="form-control" min="1990" max="2030"></div></div>
            <div class="g2"><div class="form-group"><label class="form-label">Plate Number</label><input type="text" name="plate_number" class="form-control"></div>
            <div class="form-group"><label class="form-label">Category</label>
                <select name="category_id" class="form-control"><option value="">None</option>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->category_name }}</option>@endforeach</select>
            </div></div>
            <div class="g2"><div class="form-group"><label class="form-label">Type *</label>
                <select name="type" class="form-control" required>@foreach(['Sedan','SUV','Pickup Truck','Van','Hatchback','Crossover'] as $t)<option>{{ $t }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">Transmission *</label>
                <select name="transmission" class="form-control" required><option>Automatic</option><option>Manual</option></select></div></div>
            <div class="g2"><div class="form-group"><label class="form-label">Fuel *</label>
                <select name="fuel" class="form-control" required><option>Gasoline</option><option>Diesel</option><option>Electric</option><option>Hybrid</option></select></div>
            <div class="form-group"><label class="form-label">Capacity *</label><input type="number" name="capacity" class="form-control" value="5" min="1" max="20" required></div></div>
            <div class="g2"><div class="form-group"><label class="form-label">Price per Day (PHP) *</label><input type="number" name="price_per_day" class="form-control" step="0.01" required></div>
            <div class="form-group"><label class="form-label">Status *</label>
                <select name="status" class="form-control" required><option value="available">Available</option><option value="rented">Rented</option><option value="maintenance">Maintenance</option><option value="unavailable">Unavailable</option></select></div></div>
            <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
            <div class="form-group"><label class="form-label">Image</label><input type="file" name="image" class="form-control" accept="image/*" style="height:auto;padding:10px"></div>
            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px">Add Vehicle</button>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;align-items:center;justify-content:center;padding:20px">
    <div style="background:#0d1128;border:1px solid rgba(255,255,255,.1);border-radius:20px;width:100%;max-width:640px;max-height:90vh;overflow-y:auto;padding:28px">
        <div style="display:flex;justify-content:space-between;margin-bottom:20px">
            <h2 style="font-size:1.1rem;font-weight:800">Edit Vehicle</h2>
            <button onclick="document.getElementById('editModal').style.display='none'" style="background:none;border:none;cursor:pointer;color:rgba(240,242,255,.5);font-size:1.2rem">✕</button>
        </div>
        <form method="POST" id="editForm" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="g2"><div class="form-group"><label class="form-label">Name *</label><input type="text" name="name" id="eName" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Brand</label><input type="text" name="brand" id="eBrand" class="form-control"></div></div>
            <div class="g2"><div class="form-group"><label class="form-label">Model</label><input type="text" name="model" id="eModel" class="form-control"></div>
            <div class="form-group"><label class="form-label">Year</label><input type="number" name="year" id="eYear" class="form-control"></div></div>
            <div class="g2"><div class="form-group"><label class="form-label">Plate Number</label><input type="text" name="plate_number" id="ePlate" class="form-control"></div>
            <div class="form-group"><label class="form-label">Price per Day (PHP) *</label><input type="number" name="price_per_day" id="ePrice" class="form-control" step="0.01" required></div></div>
            <div class="g2"><div class="form-group"><label class="form-label">Type *</label>
                <select name="type" id="eType" class="form-control" required>@foreach(['Sedan','SUV','Pickup Truck','Van','Hatchback','Crossover'] as $t)<option>{{ $t }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">Transmission *</label>
                <select name="transmission" id="eTrans" class="form-control" required><option>Automatic</option><option>Manual</option></select></div></div>
            <div class="g2"><div class="form-group"><label class="form-label">Fuel *</label>
                <select name="fuel" id="eFuel" class="form-control" required><option>Gasoline</option><option>Diesel</option><option>Electric</option><option>Hybrid</option></select></div>
            <div class="form-group"><label class="form-label">Status *</label>
                <select name="status" id="eStatus" class="form-control" required><option value="available">Available</option><option value="rented">Rented</option><option value="maintenance">Maintenance</option><option value="unavailable">Unavailable</option></select></div></div>
            <div class="form-group"><label class="form-label">Description</label><textarea name="description" id="eDesc" class="form-control" rows="2"></textarea></div>
            <div class="form-group"><label class="form-label">New Image (leave blank to keep current)</label><input type="file" name="image" class="form-control" accept="image/*" style="height:auto;padding:10px"></div>
            <div class="form-group"><label class="form-label">Capacity *</label><input type="number" name="capacity" id="eCap" class="form-control" min="1" max="20" required></div>
            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px">Save Changes</button>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
function openEdit(v){
    document.getElementById('editForm').action='/admin/vehicles/'+v.id;
    document.getElementById('eName').value=v.name||'';
    document.getElementById('eBrand').value=v.brand||'';
    document.getElementById('eModel').value=v.model||'';
    document.getElementById('eYear').value=v.year||'';
    document.getElementById('ePlate').value=v.plate_number||'';
    document.getElementById('ePrice').value=v.price_per_day||'';
    document.getElementById('eType').value=v.type||'';
    document.getElementById('eTrans').value=v.transmission||'';
    document.getElementById('eFuel').value=v.fuel||'';
    document.getElementById('eStatus').value=v.status||'';
    document.getElementById('eDesc').value=v.description||'';
    document.getElementById('eCap').value=v.capacity||5;
    document.getElementById('editModal').style.display='flex';
}
</script>
@endpush
