@extends('layouts.app')
@section('title','Terms & Conditions')
@section('page-title','Terms & Conditions')
@section('content')
<div style="max-width:860px">
    @if($terms)
    <div class="card" style="margin-bottom:20px">
        <div class="card-header"><span class="card-title">Current Version</span>
            <span style="font-size:.8rem;color:var(--text-dim)">Last updated {{ $terms->updated_at?->format('M d, Y H:i') }}</span>
        </div>
        <div class="card-body" style="font-size:.9rem;color:var(--muted);line-height:1.7">
            {!! $terms->content !!}
        </div>
    </div>
    @endif
    <div class="card">
        <div class="card-header"><span class="card-title">{{ $terms ? 'Update' : 'Create' }} Terms & Conditions</span></div>
        <div class="card-body">
            <form method="POST" action="{{ route('super-admin.terms.update') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Content (HTML supported)</label>
                    <textarea name="content" class="form-control" rows="16" required placeholder="<h2>Terms...</h2><p>...</p>">{{ old('content', $terms?->content) }}</textarea>
                    @error('content')<p style="color:#f87171;font-size:.82rem;margin-top:6px">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="btn btn-primary">Save Terms & Conditions</button>
            </form>
        </div>
    </div>
</div>
@endsection
