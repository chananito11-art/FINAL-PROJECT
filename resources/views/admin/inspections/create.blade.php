@extends('layouts.app')
@section('title', 'Vehicle Inspection — Booking #' . $booking->id)
@section('page-title', 'Vehicle Inspection (' . ucfirst($type) . ')')

@section('content')
<div style="max-width:800px;margin:0 auto">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Inspection Details</span>
            <span class="badge {{ $type === 'pickup' ? 'bg_' : 'bo' }}">{{ ucfirst($type) }}</span>
        </div>
        <form action="{{ route('admin.bookings.inspection.store', $booking) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="card-body">
                <div class="g2">
                    <div class="form-group">
                        <label class="form-label">Odometer Reading (km)</label>
                        <input type="number" name="odometer_reading" class="form-control" value="{{ $booking->vehicle->odometer }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fuel Level (%)</label>
                        <input type="number" name="fuel_level" class="form-control" min="0" max="100" value="100" required>
                        <p style="font-size:.75rem;color:var(--muted);margin-top:4px">Enter percentage (e.g. 100 for full, 50 for half).</p>
                    </div>
                </div>

                <div class="g2">
                    <div class="form-group">
                        <label class="form-label">Exterior Condition</label>
                        <select name="exterior_condition" class="form-control" required>
                            <option>Good (No visible damage)</option>
                            <option>Scratched</option>
                            <option>Dented</option>
                            <option>Damaged Lights/Glass</option>
                            <option>Other (See Notes)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Interior Condition</label>
                        <select name="interior_condition" class="form-control" required>
                            <option>Clean & Intact</option>
                            <option>Stained Upholstery</option>
                            <option>Odors (Smoke/Pets)</option>
                            <option>Damaged Fittings</option>
                            <option>Other (See Notes)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Additional Notes / Remarks</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Describe any existing or new damage…"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Inspection Photos</label>
                    <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                    <p style="font-size:.75rem;color:var(--muted);margin-top:6px">You can upload multiple photos of the vehicle status.</p>
                </div>
            </div>
            <div style="padding:16px 22px;background:var(--ghost-bg);border-top:1px solid var(--line);display:flex;justify-content:flex-end;gap:12px">
                <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Inspection Report</button>
            </div>
        </form>
    </div>
</div>
@endsection
