@extends('layouts.app')
@section('title', 'Identity Verification')
@section('page-title', 'Complete Your Profile')

@push('styles')
<style>
    /* Upload drop zone */
    .upload-zone {
        border: 2px dashed var(--line);
        border-radius: 14px;
        padding: 28px 20px;
        text-align: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        position: relative;
    }
    .upload-zone:hover, .upload-zone.dragover {
        border-color: var(--orange);
        background: rgba(255,107,0,.04);
    }
    .upload-zone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }
    .upload-zone-label {
        font-size: .85rem;
        color: var(--muted);
        pointer-events: none;
    }
    .upload-zone-label strong {
        color: var(--orange-l);
    }

    /* Preview */
    #previewWrap {
        display: none;
        margin-top: 14px;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--line);
        position: relative;
    }
    #previewWrap img {
        width: 100%;
        max-height: 260px;
        object-fit: contain;
        background: var(--ghost-bg);
        display: block;
    }
    #previewWrap .preview-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        background: rgba(0,0,0,.6);
        color: #fff;
        font-size: .72rem;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
    }
    #previewWrap .remove-preview {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(239,68,68,.85);
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: .72rem;
        font-weight: 700;
        padding: 4px 8px;
        cursor: pointer;
        line-height: 1;
    }

    /* Doc thumbnail in table */
    .doc-thumb {
        width: 48px;
        height: 36px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid var(--line);
        cursor: pointer;
        transition: transform .15s, box-shadow .15s;
        vertical-align: middle;
    }
    .doc-thumb:hover {
        transform: scale(1.08);
        box-shadow: 0 4px 14px rgba(0,0,0,.4);
    }

    /* Lightbox */
    #lightbox {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.85);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(6px);
    }
    #lightbox.active { display: flex; }
    #lightbox img {
        max-width: 90vw;
        max-height: 88vh;
        border-radius: 14px;
        box-shadow: 0 24px 60px rgba(0,0,0,.6);
    }
    #lightbox .lb-close {
        position: fixed;
        top: 20px;
        right: 24px;
        background: rgba(255,255,255,.12);
        border: none;
        color: #fff;
        font-size: 1.4rem;
        line-height: 1;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .2s;
    }
    #lightbox .lb-close:hover { background: rgba(255,255,255,.22); }
    #lightbox .lb-label {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0,0,0,.6);
        color: #fff;
        font-size: .82rem;
        padding: 6px 14px;
        border-radius: 8px;
        white-space: nowrap;
    }
</style>
@endpush

@section('content')
<div style="max-width:900px;margin:0 auto">

    {{-- Status Banner --}}
    <div style="background:var(--ghost-bg);border:1px solid var(--line);border-radius:16px;padding:24px;margin-bottom:32px;display:flex;align-items:center;gap:20px">
        <div style="width:60px;height:60px;border-radius:50%;background:{{ $user->verification_status === 'verified' ? '#22c55e20' : ($user->verification_status === 'rejected' ? '#ef444420' : 'var(--orange-l)20') }};display:flex;align-items:center;justify-content:center;color:{{ $user->verification_status === 'verified' ? '#22c55e' : ($user->verification_status === 'rejected' ? '#ef4444' : 'var(--orange)') }}">
            @if($user->verification_status === 'verified')
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            @elseif($user->verification_status === 'rejected')
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            @else
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            @endif
        </div>
        <div>
            <h2 style="font-size:1.2rem;margin-bottom:4px">Account Status: <span style="text-transform:capitalize">{{ $user->verification_status }}</span></h2>
            <p style="font-size:.9rem;color:var(--muted)">
                @if($user->verification_status === 'unverified')
                    Please upload your Driver's License to start booking vehicles.
                @elseif($user->verification_status === 'pending')
                    Your documents are being reviewed. This usually takes 1-2 hours.
                @elseif($user->verification_status === 'verified')
                    Success! You can now book any vehicle with instant approval.
                @elseif($user->verification_status === 'expired')
                    Your ID has expired. Please upload a new one to continue using our services.
                @endif
            </p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1.2fr;gap:32px">
        {{-- Upload Form --}}
        <div>
            <div class="card">
                <div class="card-header"><span class="card-title">Upload Documents</span></div>
                <form action="{{ route('customer.verification.store') }}" method="POST" enctype="multipart/form-data" id="verifyForm">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">Document Type</label>
                            <input type="text" class="form-control" value="Driver's License" readonly
                                style="opacity:.7;cursor:not-allowed;background:var(--ghost-bg)">
                            <input type="hidden" name="document_type" value="Driver's License">
                            <p style="font-size:.75rem;color:var(--muted);margin-top:6px">
                                ⚠ Only a valid <strong>Driver's License</strong> is accepted for verification.
                            </p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">ID Expiration Date</label>
                            <input type="date" name="expiration_date" class="form-control" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Upload Clear Photo</label>

                            {{-- Drop zone --}}
                            <div class="upload-zone" id="uploadZone">
                                <input type="file" name="file" id="fileInput" accept="image/*" required>
                                <div class="upload-zone-label">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:8px;opacity:.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    <br>
                                    <strong>Click to browse</strong> or drag &amp; drop your photo here
                                    <br>
                                    <span style="font-size:.72rem">JPG, PNG, WEBP — max 5 MB</span>
                                </div>
                            </div>

                            {{-- Live Preview --}}
                            <div id="previewWrap">
                                <span class="preview-badge">📷 Preview</span>
                                <img id="previewImg" src="" alt="Preview">
                                <button type="button" class="remove-preview" id="removePreview">✕ Remove</button>
                            </div>

                            <p style="font-size:.75rem;color:var(--muted);margin-top:8px">Please ensure all text and your photo are clearly visible.</p>
                        </div>
                    </div>
                    <div style="padding:16px 22px;background:var(--ghost-bg);border-top:1px solid var(--line)">
                        <button type="submit" class="btn btn-primary" style="width:100%" {{ $user->verification_status === 'pending' ? 'disabled' : '' }}>
                            {{ $user->verification_status === 'pending' ? 'Verification in Progress' : 'Submit for Review' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- History --}}
        <div>
            <div class="card">
                <div class="card-header"><span class="card-title">Document History</span></div>
                <div class="tw">
                    <table>
                        <thead><tr><th>Photo</th><th>Type</th><th>Expires</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($documents as $doc)
                            <tr>
                                <td>
                                    <img
                                        src="{{ \Illuminate\Support\Facades\Storage::url($doc->file_path) }}"
                                        alt="ID Photo"
                                        class="doc-thumb"
                                        data-src="{{ \Illuminate\Support\Facades\Storage::url($doc->file_path) }}"
                                        data-label="{{ $doc->document_type }} · Submitted {{ $doc->created_at->format('M d, Y') }}"
                                        onclick="openLightbox(this)"
                                    >
                                </td>
                                <td>{{ $doc->document_type }}</td>
                                <td style="font-size:.8rem;color:var(--muted)">{{ $doc->expiration_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge {{ $doc->status === 'approved' ? 'bg_' : ($doc->status === 'rejected' ? 'br' : 'bo') }}">
                                        {{ ucfirst($doc->status) }}
                                    </span>
                                </td>
                            </tr>
                            @if($doc->admin_notes)
                            <tr><td colspan="4" style="font-size:.75rem;color:var(--red);background:rgba(239,68,68,.05)">Note: {{ $doc->admin_notes }}</td></tr>
                            @endif
                            @empty
                            <tr><td colspan="4" style="text-align:center;padding:32px;color:var(--muted)">No documents uploaded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Lightbox --}}
<div id="lightbox" onclick="closeLightbox(event)">
    <button class="lb-close" onclick="closeLightbox()">✕</button>
    <img id="lightboxImg" src="" alt="Document">
    <span class="lb-label" id="lightboxLabel"></span>
</div>
@endsection

@push('scripts')
<script>
const fileInput   = document.getElementById('fileInput');
const previewWrap = document.getElementById('previewWrap');
const previewImg  = document.getElementById('previewImg');
const uploadZone  = document.getElementById('uploadZone');
const removeBtn   = document.getElementById('removePreview');

// Show preview when file chosen
fileInput.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        previewImg.src = e.target.result;
        previewWrap.style.display = 'block';
    };
    reader.readAsDataURL(file);
});

// Remove / clear preview
removeBtn.addEventListener('click', function () {
    fileInput.value = '';
    previewImg.src = '';
    previewWrap.style.display = 'none';
});

// Drag & drop highlight
uploadZone.addEventListener('dragover',  e => { e.preventDefault(); uploadZone.classList.add('dragover'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
uploadZone.addEventListener('drop',      e => { e.preventDefault(); uploadZone.classList.remove('dragover'); });

// Lightbox
function openLightbox(el) {
    document.getElementById('lightboxImg').src   = el.dataset.src;
    document.getElementById('lightboxLabel').textContent = el.dataset.label ?? '';
    document.getElementById('lightbox').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeLightbox(e) {
    if (e && e.target !== document.getElementById('lightbox') && !e.target.classList.contains('lb-close')) return;
    document.getElementById('lightbox').classList.remove('active');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox({ target: document.getElementById('lightbox') }); });
</script>
@endpush
