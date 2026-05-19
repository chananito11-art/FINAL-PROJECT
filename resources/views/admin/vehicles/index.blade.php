@extends('layouts.app')
@section('title','Vehicles')
@section('page-title','Vehicle Management')
@section('content')
<div style="display:flex;justify-content:flex-end;margin-bottom:16px">
    <button onclick="document.getElementById('addModal').style.display='flex'" class="btn btn-primary">+ Add Vehicle</button>
</div>
<div class="card">
    <div class="card-header"><span class="card-title">All Vehicles</span><span style="font-size:.85rem;color:var(--text-dim)">{{ $vehicles->total() }} total</span></div>
    <div class="tw">
        <table>
            <thead><tr><th>Vehicle</th><th>Type</th><th>Brand</th><th>Price/Day</th><th>Status</th><th>Plate</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($vehicles as $v)
            <tr>
                <td style="font-weight:600">{{ $v->name }}</td>
                <td><span class="badge bgy">{{ $v->type }}</span></td>
                <td style="color:var(--muted)">{{ $v->brand }}</td>
                <td style="color:#ff8c3a;font-weight:700">₱{{ number_format($v->price_per_day,0) }}</td>
                <td>
                    @php $sc=['available'=>'bg_','rented'=>'bo','maintenance'=>'by','unavailable'=>'br']; @endphp
                    <span class="badge {{ $sc[$v->status]??'bgy' }}">{{ ucfirst($v->status) }}</span>
                </td>
                <td style="font-size:.85rem;color:var(--text-dim)">{{ $v->plate_number ?? '—' }}</td>
                <td>
                    <button onclick='openEdit({{ $v->toJson() }})' class="btn btn-ghost btn-sm">Edit</button>
                    @if($v->status !== 'unavailable')
                    <form method="POST" action="{{ route('admin.vehicles.destroy',$v) }}" style="display:inline" onsubmit="return confirm('Mark this vehicle as unavailable? This preserves historical data while preventing new bookings.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" title="Mark as Unavailable">Deactivate</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-dim)">No vehicles yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($vehicles->hasPages())<div style="padding:16px;border-top:1px solid var(--line)">{{ $vehicles->links() }}</div>@endif
</div>

{{-- Add Modal --}}
<div id="addModal" class="modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:1000;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(4px);transition:all .3s ease">
    <div class="card" style="width:100%;max-width:640px;max-height:90vh;overflow-y:auto;transform:translateY(30px);transition:all .3s ease">
        <div class="card-header">
            <span class="card-title">Add New Vehicle</span>
            <button onclick="closeModal('addModal')" style="background:none;border:none;cursor:pointer;color:var(--text-dim);font-size:1.2rem">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.vehicles.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="g2"><div class="form-group"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" placeholder="e.g. Civic" required></div>
                <div class="form-group"><label class="form-label">Brand</label><input type="text" name="brand" class="form-control" placeholder="e.g. Honda"></div></div>
                <div class="g2"><div class="form-group"><label class="form-label">Model</label><input type="text" name="model" class="form-control" placeholder="e.g. RS Turbo"></div>
                <div class="form-group"><label class="form-label">Year</label><input type="number" name="year" class="form-control" min="1990" max="2030" placeholder="2024"></div></div>
                <div class="g2"><div class="form-group"><label class="form-label">Plate Number</label><input type="text" name="plate_number" class="form-control" placeholder="ABC 1234"></div>
                <div class="form-group"><label class="form-label">Capacity *</label><input type="number" name="capacity" class="form-control" value="5" min="1" max="20" required></div></div>
                <div class="g2"><div class="form-group"><label class="form-label">Type *</label>
                    <select name="type" class="form-control" required>@foreach(['Sedan','SUV','Pickup Truck','Van','Hatchback','Crossover'] as $t)<option>{{ $t }}</option>@endforeach</select></div>
                <div class="form-group"><label class="form-label">Transmission *</label>
                    <select name="transmission" class="form-control" required><option>Automatic</option><option>Manual</option></select></div></div>
                <div class="g2"><div class="form-group"><label class="form-label">Fuel *</label>
                    <select name="fuel" class="form-control" required><option>Gasoline</option><option>Diesel</option><option>Electric</option><option>Hybrid</option></select></div>
                <div class="form-group"><label class="form-label">Price per Day (PHP) *</label><input type="number" name="price_per_day" class="form-control" step="0.01" placeholder="0.00" required></div></div>
                <div class="form-group"><label class="form-label">Status *</label>
                    <select name="status" class="form-control" required><option value="available">Available</option><option value="rented">Rented</option><option value="maintenance">Maintenance</option><option value="unavailable">Unavailable</option></select></div>
                <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3" placeholder="Additional details about the vehicle..."></textarea></div>
                <div class="form-group"><label class="form-label">Vehicle Image</label><input type="file" name="image" class="form-control" accept="image/*" style="height:auto;padding:12px"></div>
            </div>
            <div style="padding:20px 24px; background:var(--ghost-bg); border-top:1px solid var(--line); display:flex; justify-content:flex-end; gap:12px">
                <button type="button" onclick="closeModal('addModal')" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Vehicle</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editModal" class="modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:1000;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(4px);transition:all .3s ease">
    <div class="card" style="width:100%;max-width:640px;max-height:90vh;overflow-y:auto;transform:translateY(30px);transition:all .3s ease">
        <div class="card-header">
            <span class="card-title">Edit Vehicle</span>
            <button onclick="closeModal('editModal')" style="background:none;border:none;cursor:pointer;color:var(--text-dim);font-size:1.2rem">✕</button>
        </div>
        <form method="POST" id="editForm" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="card-body">
                <div class="g2"><div class="form-group"><label class="form-label">Name *</label><input type="text" name="name" id="eName" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Brand</label><input type="text" name="brand" id="eBrand" class="form-control"></div></div>
                <div class="g2"><div class="form-group"><label class="form-label">Model</label><input type="text" name="model" id="eModel" class="form-control"></div>
                <div class="form-group"><label class="form-label">Year</label><input type="number" name="year" id="eYear" class="form-control"></div></div>
                <div class="g2"><div class="form-group"><label class="form-label">Plate Number</label><input type="text" name="plate_number" id="ePlate" class="form-control"></div>
                <div class="form-group"><label class="form-label">Capacity *</label><input type="number" name="capacity" id="eCap" class="form-control" min="1" max="20" required></div></div>
                <div class="g2"><div class="form-group"><label class="form-label">Type *</label>
                    <select name="type" id="eType" class="form-control" required>@foreach(['Sedan','SUV','Pickup Truck','Van','Hatchback','Crossover'] as $t)<option>{{ $t }}</option>@endforeach</select></div>
                <div class="form-group"><label class="form-label">Transmission *</label>
                    <select name="transmission" id="eTrans" class="form-control" required><option>Automatic</option><option>Manual</option></select></div></div>
                <div class="g2"><div class="form-group"><label class="form-label">Fuel *</label>
                    <select name="fuel" id="eFuel" class="form-control" required><option>Gasoline</option><option>Diesel</option><option>Electric</option><option>Hybrid</option></select></div>
                <div class="form-group"><label class="form-label">Price per Day (PHP) *</label><input type="number" name="price_per_day" id="ePrice" class="form-control" step="0.01" required></div></div>
                <div class="form-group"><label class="form-label">Status *</label>
                    <select name="status" id="eStatus" class="form-control" required><option value="available">Available</option><option value="rented">Rented</option><option value="maintenance">Maintenance</option><option value="unavailable">Unavailable</option></select></div>
                <div class="form-group"><label class="form-label">Description</label><textarea name="description" id="eDesc" class="form-control" rows="3"></textarea></div>
                <div class="form-group"><label class="form-label">New Image (leave blank to keep current)</label><input type="file" name="image" class="form-control" accept="image/*" style="height:auto;padding:12px"></div>
            </div>
            <div style="padding:20px 24px; background:var(--ghost-bg); border-top:1px solid var(--line); display:flex; justify-content:flex-end; gap:12px">
                <button type="button" onclick="closeModal('editModal')" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
function closeModal(id) {
    const modal = document.getElementById(id);
    modal.style.opacity = '0';
    modal.querySelector('.card').style.transform = 'translateY(30px)';
    setTimeout(() => { modal.style.display = 'none'; }, 300);
}

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
    
    const modal = document.getElementById('editModal');
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.style.opacity = '1';
        modal.querySelector('.card').style.transform = 'translateY(0)';
    }, 10);
}

// Add vehicle button logic
document.querySelector('button[onclick*="addModal"]').onclick = function() {
    const modal = document.getElementById('addModal');
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.style.opacity = '1';
        modal.querySelector('.card').style.transform = 'translateY(0)';
    }, 10);
};
</script>
@endpush
