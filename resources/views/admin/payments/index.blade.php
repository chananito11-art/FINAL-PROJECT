@extends('layouts.app')
@section('title','Payment Verification')
@section('page-title','Payment Verification')
@section('content')
<div class="card">
    <div class="card-header"><span class="card-title">Pending Payments</span><span style="font-size:.85rem;color:rgba(240,242,255,.45)">{{ $payments->total() }} pending</span></div>
    <div class="tw">
        <table>
            <thead><tr><th>Booking #</th><th>Customer</th><th>Vehicle</th><th>Amount</th><th>Reference</th><th>Screenshot</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($payments as $pay)
            <tr>
                <td style="color:rgba(240,242,255,.45)">#{{ $pay->booking_id }}</td>
                <td><div style="font-weight:600">{{ $pay->booking->user?->first_name }} {{ $pay->booking->user?->last_name }}</div><div style="font-size:.8rem;color:rgba(240,242,255,.45)">{{ $pay->booking->user?->email }}</div></td>
                <td>{{ $pay->booking->vehicle?->name }}</td>
                <td style="color:#ff8c3a;font-weight:700">₱{{ number_format($pay->amount,0) }}</td>
                <td><code style="font-size:.82rem;color:#60a5fa">{{ $pay->reference_code }}</code></td>
                <td>
                    <a href="{{ $pay->screenshot_url }}" target="_blank">
                        <img src="{{ $pay->screenshot_url }}" style="width:64px;height:48px;object-fit:cover;border-radius:6px;border:1px solid rgba(255,255,255,.1)" alt="proof">
                    </a>
                </td>
                <td>
                    <form method="POST" action="{{ route('admin.payments.verify',$pay) }}" style="display:inline">@csrf
                        <button type="submit" class="btn btn-success btn-sm">✓ Verify</button>
                    </form>
                    <button onclick="openReject({{ $pay->id }})" class="btn btn-danger btn-sm">✕ Reject</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:40px;color:rgba(240,242,255,.4)">No pending payments.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())<div style="padding:16px;border-top:1px solid rgba(255,255,255,.06)">{{ $payments->links() }}</div>@endif
</div>

<div id="rejectModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;align-items:center;justify-content:center">
    <div style="background:#0d1128;border:1px solid rgba(255,255,255,.1);border-radius:18px;width:100%;max-width:440px;padding:28px">
        <h2 style="font-size:1.05rem;font-weight:800;margin-bottom:16px">Reject Payment</h2>
        <form method="POST" id="rejectForm">@csrf
            <div class="form-group">
                <label class="form-label">Rejection Reason *</label>
                <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Explain why the payment is rejected…" required></textarea>
            </div>
            <div style="display:flex;gap:10px;margin-top:8px">
                <button type="button" onclick="document.getElementById('rejectModal').style.display='none'" class="btn btn-ghost" style="flex:1">Cancel</button>
                <button type="submit" class="btn btn-danger" style="flex:1">Reject</button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
function openReject(id){
    document.getElementById('rejectForm').action='/admin/payments/'+id+'/reject';
    document.getElementById('rejectModal').style.display='flex';
}
</script>
@endpush
