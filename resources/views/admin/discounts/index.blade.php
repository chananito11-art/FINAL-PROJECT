@extends('layouts.app')
@section('title', 'Discount Codes')
@section('page-title', 'Manage Discounts')

@section('content')
<div style="display:grid;grid-template-columns:1fr 2fr;gap:24px">
    {{-- Create Discount --}}
    <div>
        <div class="card">
            <div class="card-header"><span class="card-title">Create New Discount</span></div>
            <form action="{{ route('admin.discounts.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Discount Code</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. SUMMER20" required style="text-transform:uppercase">
                    </div>
                    <div class="g2">
                        <div class="form-group">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-control" required>
                                <option value="percent">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (PHP)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Value</label>
                            <input type="number" step="0.01" name="value" class="form-control" required>
                        </div>
                    </div>
                    <div class="g2">
                        <div class="form-group">
                            <label class="form-label">Starts At</label>
                            <input type="date" name="starts_at" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Expires At</label>
                            <input type="date" name="expires_at" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Usage Limit (Total)</label>
                        <input type="number" name="usage_limit" class="form-control" placeholder="Optional">
                    </div>
                </div>
                <div style="padding:16px 22px;background:var(--ghost-bg);border-top:1px solid var(--line)">
                    <button type="submit" class="btn btn-primary" style="width:100%">Create Discount</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Discounts List --}}
    <div>
        <div class="card">
            <div class="tw">
                <table>
                    <thead><tr><th>Code</th><th>Value</th><th>Validity</th><th>Usage</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($discounts as $d)
                        <tr>
                            <td><strong style="color:var(--orange)">{{ $d->code }}</strong></td>
                            <td>{{ $d->type === 'percent' ? $d->value.'%' : '₱'.number_format($d->value, 0) }}</td>
                            <td style="font-size:.8rem;color:var(--muted)">
                                @if($d->expires_at) {{ $d->expires_at->format('M d, Y') }} @else Perpetual @endif
                            </td>
                            <td>{{ $d->times_used }} / {{ $d->usage_limit ?: '∞' }}</td>
                            <td>
                                <form action="{{ route('admin.discounts.toggle', $d) }}" method="POST">
                                    @csrf
                                    <button type="submit" style="background:none;border:none;padding:0;cursor:pointer">
                                        <span class="badge {{ $d->is_active ? 'bg_' : 'br' }}">{{ $d->is_active ? 'Active' : 'Inactive' }}</span>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form action="{{ route('admin.discounts.destroy', $d) }}" method="POST" onsubmit="return confirm('Delete this code?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-ghost btn-sm" style="color:var(--red)">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--muted)">No discounts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($discounts->hasPages())<div style="padding:12px 22px;border-top:1px solid var(--line)">{{ $discounts->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
