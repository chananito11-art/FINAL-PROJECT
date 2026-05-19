@extends('layouts.app')
@section('title', 'Review Verification — ' . $user->name)
@section('page-title', 'Review Documents')

@section('content')
<div style="margin-bottom:24px">
    <a href="{{ route('admin.verification.index') }}" style="color:var(--muted);text-decoration:none;font-size:.9rem">← Back to Pending List</a>
</div>

<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:32px">
    {{-- Document Viewer --}}
    <div>
        @foreach($user->documents->where('status', 'pending') as $doc)
        <div class="card" style="margin-bottom:24px">
            <div class="card-header"><span class="card-title">{{ $doc->document_type }}</span></div>
            <div class="card-body">
                <div style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:center">
                    <span style="color:var(--muted)">Expires: <strong>{{ $doc->expiration_date->format('M d, Y') }}</strong></span>
                    <a href="{{ \Illuminate\Support\Facades\Storage::url($doc->file_path) }}" target="_blank" class="btn btn-ghost btn-sm">Open in New Tab</a>
                </div>
                <img src="{{ \Illuminate\Support\Facades\Storage::url($doc->file_path) }}" style="width:100%; max-height:480px; object-fit:contain; background:var(--ghost-bg); border-radius:12px; border:1px solid var(--line)">
            </div>
        </div>
        @endforeach

        @if($user->documents->where('status', '!=', 'pending')->count() > 0)
        <div class="card">
            <div class="card-header"><span class="card-title">Previous Documents</span></div>
            <div class="tw">
                <table>
                    <thead><tr><th>Type</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                        @foreach($user->documents->where('status', '!=', 'pending') as $old)
                        <tr>
                            <td>{{ $old->document_type }}</td>
                            <td><span class="badge {{ $old->status === 'approved' ? 'bg_' : 'br' }}">{{ ucfirst($old->status) }}</span></td>
                            <td style="font-size:.8rem;color:var(--muted)">{{ $old->updated_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- Decision Box --}}
    <div>
        <div class="card" style="position:sticky;top:24px">
            <div class="card-header"><span class="card-title">Final Decision</span></div>
            <form action="{{ route('admin.verification.verify', $user) }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Update Verification Status</label>
                        <select name="status" class="form-control" required onchange="this.value === 'rejected' ? document.getElementById('notes_box').required = true : null">
                            <option value="verified">✓ Approve & Verify Account</option>
                            <option value="rejected">✗ Reject Application</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Internal / Customer Notes</label>
                        <textarea name="notes" id="notes_box" class="form-control" rows="4" placeholder="If rejecting, please explain why so the customer can fix it..."></textarea>
                    </div>
                </div>
                <div style="padding:16px 22px;background:var(--ghost-bg);border-top:1px solid var(--line)">
                    <button type="submit" class="btn btn-primary" style="width:100%">Submit Decision</button>
                    <p style="font-size:.7rem;color:var(--muted);text-align:center;margin-top:12px">This will notify the customer and update their booking privileges.</p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
