@extends('layouts.app')
@section('title','Audit Logs')
@section('page-title','Audit Logs')
@section('content')
<div class="card">
    <div class="card-header"><span class="card-title">Audit Logs</span><span style="font-size:.85rem;color:rgba(240,242,255,.45)">{{ $logs->total() }} entries</span></div>
    <div class="tw">
        <table>
            <thead><tr><th>When</th><th>Who</th><th>What</th><th>Where</th><th>Why</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
            <tr>
                <td style="font-size:.8rem;color:rgba(240,242,255,.45);white-space:nowrap">{{ $log->created_at?->format('M d, Y H:i') }}</td>
                <td>
                    @if($log->user)
                    <div style="font-size:.88rem;font-weight:600">{{ $log->user->first_name }} {{ $log->user->last_name }}</div>
                    <div style="font-size:.75rem;color:rgba(240,242,255,.4)">{{ $log->user->email }}</div>
                    @else
                    <span style="color:rgba(240,242,255,.35);font-size:.85rem">System</span>
                    @endif
                </td>
                <td>
                    <div style="font-size:.88rem">{{ $log->action }}</div>
                    <div style="font-size:.75rem;color:rgba(240,242,255,.45)">
                        @if($log->model_type)<span>{{ class_basename($log->model_type) }} #{{ $log->model_id }}</span>@endif
                    </div>
                </td>
                <td>
                    <div style="font-size:.8rem">{{ $log->ip_address ?? '—' }}</div>
                    <div style="font-size:.75rem;color:rgba(240,242,255,.4)">{{ $log->url }}</div>
                </td>
                <td style="font-size:.85rem;color:rgba(240,242,255,.7)">{{ $log->details ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;padding:40px;color:rgba(240,242,255,.4)">No activity logs yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())<div style="padding:16px;border-top:1px solid rgba(255,255,255,.06)">{{ $logs->links() }}</div>@endif
</div>
@endsection
