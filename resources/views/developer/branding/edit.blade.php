@extends('layouts.sec')

@section('title', 'Branding Settings')

@section('styles')
<style>
    .branding-section { border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.25rem; margin-bottom: 1.25rem; background: #fff; }
    .branding-section h5 { border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; margin-bottom: 1rem; }
    .preview-box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem; background: #f8fafc; }
    .preview-img { max-height: 80px; object-fit: contain; border-radius: 4px; }
    .preview-img-banner { max-height: 100px; object-fit: contain; border-radius: 4px; }
    .color-preview { width: 36px; height: 36px; border-radius: 6px; border: 1px solid #cbd5e1; display: inline-block; vertical-align: middle; }
    .sidebar-mockup { background: #1e293b; border-radius: 8px; padding: 1rem; color: #cbd5e1; min-height: 200px; }
    .sidebar-mockup .brand { color: #fff; font-weight: 700; font-size: 1.1rem; }
    .sidebar-mockup .subtitle { font-size: 0.8rem; color: #94a3b8; }
    .sidebar-mockup .nav-item { padding: 0.4rem 0.6rem; border-radius: 6px; margin-bottom: 2px; font-size: 0.85rem; }
    .sidebar-mockup .nav-item.active { background: #3b82f6; color: #fff; }
    .sidebar-mockup .nav-item.hover { background: #334155; color: #f8fafc; }
</style>
@endsection

@section('content')
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0">Branding Settings</h4>
            <p class="text-muted mb-0 small">Customize system-wide appearance</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('developer.branding.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- === BANNER SECTION === --}}
        <div class="branding-section">
            <h5>Application Banner</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="fw-semibold small text-muted">Current Banner</label>
                    <div class="preview-box mt-1">
                        <img src="{{ app(App\Services\BrandingService::class)->assetUrl('banner_path') }}" alt="Current Banner" class="preview-img-banner w-100">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold small text-muted">Original Banner</label>
                    <div class="preview-box mt-1">
                        <img src="{{ asset($originals['banner_path']) }}" alt="Original Banner" class="preview-img-banner w-100">
                    </div>
                </div>
                <div class="col-md-8">
                    <label for="banner_path" class="form-label">Upload New Banner</label>
                    <input type="file" class="form-control form-control-sm" id="banner_path" name="banner_path" accept="image/png,image/jpeg,image/webp">
                    <div class="form-text">PNG, JPG, or WebP. Max 5MB. Max 4000×2000px.</div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="restoreField('banner_path')">Restore to Default</button>
                </div>
            </div>
        </div>

        {{-- === OPAC BANNER SECTION === --}}
        <div class="branding-section">
            <h5>OPAC Banner</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="fw-semibold small text-muted">Current OPAC Banner</label>
                    <div class="preview-box mt-1">
                        <img src="{{ app(App\Services\BrandingService::class)->assetUrl('opac_banner_path') }}" alt="Current OPAC Banner" class="preview-img-banner w-100">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold small text-muted">Original OPAC Banner</label>
                    <div class="preview-box mt-1">
                        <img src="{{ asset($originals['opac_banner_path']) }}" alt="Original OPAC Banner" class="preview-img-banner w-100">
                    </div>
                </div>
                <div class="col-md-8">
                    <label for="opac_banner_path" class="form-label">Upload New OPAC Banner</label>
                    <input type="file" class="form-control form-control-sm" id="opac_banner_path" name="opac_banner_path" accept="image/png,image/jpeg,image/webp">
                    <div class="form-text">PNG, JPG, or WebP. Max 5MB. Max 4000×2000px.</div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="restoreField('opac_banner_path')">Restore to Default</button>
                </div>
            </div>
        </div>

        {{-- === OPAC LOGO SECTION === --}}
        <div class="branding-section">
            <h5>OPAC Library Logo</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="fw-semibold small text-muted">Current OPAC Logo</label>
                    <div class="preview-box mt-1">
                        <img src="{{ app(App\Services\BrandingService::class)->assetUrl('opac_logo_path') }}" alt="Current OPAC Logo" class="preview-img">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold small text-muted">Original OPAC Logo</label>
                    <div class="preview-box mt-1">
                        <img src="{{ asset($originals['opac_logo_path']) }}" alt="Original OPAC Logo" class="preview-img">
                    </div>
                </div>
                <div class="col-md-8">
                    <label for="opac_logo_path" class="form-label">Upload New OPAC Logo</label>
                    <input type="file" class="form-control form-control-sm" id="opac_logo_path" name="opac_logo_path" accept="image/png,image/jpeg,image/webp">
                    <div class="form-text">PNG, JPG, or WebP. Max 2MB. Max 1000×1000px.</div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="restoreField('opac_logo_path')">Restore to Default</button>
                </div>
            </div>
        </div>

        {{-- === OPAC DEFAULT BOOK COVER SECTION === --}}
        <div class="branding-section">
            <h5>OPAC Default Book Cover</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="fw-semibold small text-muted">Current Book Cover</label>
                    <div class="preview-box mt-1">
                        <img src="{{ app(App\Services\BrandingService::class)->assetUrl('opac_default_book_cover_path') }}" alt="Current Book Cover" class="preview-img">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold small text-muted">Original Book Cover</label>
                    <div class="preview-box mt-1">
                        <img src="{{ asset($originals['opac_default_book_cover_path']) }}" alt="Original Book Cover" class="preview-img">
                    </div>
                </div>
                <div class="col-md-8">
                    <label for="opac_default_book_cover_path" class="form-label">Upload New Book Cover</label>
                    <input type="file" class="form-control form-control-sm" id="opac_default_book_cover_path" name="opac_default_book_cover_path" accept="image/png,image/jpeg,image/webp">
                    <div class="form-text">PNG, JPG, or WebP. Max 4MB. Max 1000×1000px.</div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="restoreField('opac_default_book_cover_path')">Restore to Default</button>
                </div>
            </div>
        </div>

        {{-- === SIDEBAR LOGO SECTION === --}}
        <div class="branding-section">
            <h5>Sidebar Logo</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="fw-semibold small text-muted">Current Sidebar Logo</label>
                    <div class="preview-box mt-1">
                        <img src="{{ app(App\Services\BrandingService::class)->assetUrl('sidebar_logo_path') }}" alt="Current Sidebar Logo" class="preview-img">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold small text-muted">Original Sidebar Logo</label>
                    <div class="preview-box mt-1">
                        <img src="{{ asset($originals['sidebar_logo_path']) }}" alt="Original Sidebar Logo" class="preview-img">
                    </div>
                </div>
                <div class="col-md-8">
                    <label for="sidebar_logo_path" class="form-label">Upload New Sidebar Logo</label>
                    <input type="file" class="form-control form-control-sm" id="sidebar_logo_path" name="sidebar_logo_path" accept="image/png,image/jpeg,image/webp">
                    <div class="form-text">PNG, JPG, or WebP. Max 2MB. Max 1000×1000px.</div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="restoreField('sidebar_logo_path')">Restore to Default</button>
                </div>
            </div>
        </div>

        {{-- === SIDEBAR BRAND TEXT SECTION === --}}
        <div class="branding-section">
            <h5>Sidebar Brand Text</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="sidebar_brand_name" class="form-label">Brand Name</label>
                    <input type="text" class="form-control form-control-sm" id="sidebar_brand_name" name="sidebar_brand_name"
                           value="{{ old('sidebar_brand_name', $current['sidebar_brand_name'] ?? '') }}" maxlength="60">
                    <div class="form-text">Max 60 characters.</div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="restoreField('sidebar_brand_name')">Restore</button>
                </div>
                <div class="col-md-6">
                    <label for="sidebar_brand_subtitle" class="form-label">Brand Subtitle</label>
                    <input type="text" class="form-control form-control-sm" id="sidebar_brand_subtitle" name="sidebar_brand_subtitle"
                           value="{{ old('sidebar_brand_subtitle', $current['sidebar_brand_subtitle'] ?? '') }}" maxlength="100">
                    <div class="form-text">Max 100 characters.</div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="restoreField('sidebar_brand_subtitle')">Restore</button>
                </div>
            </div>
        </div>

        {{-- === COLOR PALETTE SECTION === --}}
        <div class="branding-section">
            <h5>Color Palette</h5>

            {{-- Live Preview Mockup --}}
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="sidebar-mockup" id="sidebar-preview" style="background-color: {{ $current['sidebar_background_color'] ?? '#1E293B' }};">
                        <div class="brand" style="color: {{ $current['sidebar_brand_text_color'] ?? '#FFFFFF' }};">
                            <span id="preview-brand-name">{{ $current['sidebar_brand_name'] ?? 'Library System' }}</span>
                            <div class="subtitle" style="color: {{ $current['sidebar_text_color'] ?? '#CBD5E1' }};">
                                <span id="preview-brand-subtitle">{{ $current['sidebar_brand_subtitle'] ?? 'Knowledge Gateway' }}</span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="nav-item active" style="background-color: {{ $current['sidebar_active_color'] ?? '#3B82F6' }}; color: #fff;">
                                📊 Dashboard
                            </div>
                            <div class="nav-item hover" style="background-color: {{ $current['sidebar_hover_background_color'] ?? '#334155' }}; color: {{ $current['sidebar_hover_text_color'] ?? '#F8FAFC' }};">
                                🎨 Branding
                            </div>
                            <div class="nav-item" style="color: {{ $current['sidebar_text_color'] ?? '#CBD5E1' }};">
                                ⚙ Settings
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 border rounded" style="background: #f8fafc;">
                        <div class="mb-2"><span class="color-preview" style="background:{{ $current['primary_color'] ?? '#2563EB' }}"></span> Primary</div>
                        <div class="mb-2"><span class="color-preview" style="background:{{ $current['secondary_color'] ?? '#475569' }}"></span> Secondary</div>
                        <div class="mb-2"><span class="color-preview" style="background:{{ $current['accent_color'] ?? '#F59E0B' }}"></span> Accent</div>
                        <div class="mb-2"><span class="color-preview" style="background:{{ $current['button_color'] ?? '#2563EB' }}"></span> Button</div>
                        <div class="mb-2"><span class="color-preview" style="background:{{ $current['table_header_color'] ?? '#1E293B' }}"></span> Table Header</div>
                    </div>
                </div>
            </div>

            {{-- Color Inputs --}}
            @php
                $colorFields = [
                    'primary_color' => 'Primary Color',
                    'secondary_color' => 'Secondary Color',
                    'accent_color' => 'Accent Color',
                    'sidebar_background_color' => 'Sidebar Background',
                    'sidebar_text_color' => 'Sidebar Text',
                    'sidebar_brand_text_color' => 'Sidebar Brand Text',
                    'sidebar_active_color' => 'Sidebar Active Item',
                    'sidebar_hover_background_color' => 'Sidebar Hover Background',
                    'sidebar_hover_text_color' => 'Sidebar Hover Text',
                    'button_color' => 'Button Color',
                    'sidebar_footer_background_color' => 'Sidebar Footer Background',
                    'table_header_color' => 'Table Header Background',
                    'table_header_text_color' => 'Table Header Text',
                    'table_border_color' => 'Table Border',
                    'table_hover_color' => 'Table Hover',
                ];
            @endphp
            <div class="row g-3">
                @foreach($colorFields as $field => $label)
                <div class="col-md-4 col-lg-3">
                    <label for="{{ $field }}" class="form-label small">{{ $label }}</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text p-1" style="background: {{ old($field, $current[$field] ?? '#000000') }};">
                            <input type="color" class="form-control form-control-color border-0 p-0" id="{{ $field }}-picker"
                                   value="{{ old($field, $current[$field] ?? '#000000') }}"
                                   oninput="document.getElementById('{{ $field }}').value = this.value.toUpperCase(); updatePreview();">
                        </span>
                        <input type="text" class="form-control form-control-sm color-hex" id="{{ $field }}"
                               name="{{ $field }}" value="{{ old($field, $current[$field] ?? '') }}"
                               pattern="^#[0-9A-Fa-f]{6}$" maxlength="7"
                               oninput="this.value = this.value.toUpperCase(); document.getElementById('{{ $field }}-picker').value = this.value; updatePreview();">
                        <button class="btn btn-outline-warning btn-sm" type="button" onclick="restoreField('{{ $field }}')" title="Restore to default">
                            ↺
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- === ACTIONS === --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <button type="submit" class="btn btn-primary" onclick="return checkContrast();">
                    <i class="bi bi-check-lg"></i> Save Changes
                </button>
                <a href="{{ route('developer.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
            <div>
                <button type="button" class="btn btn-outline-danger" onclick="confirmRestoreAll()">
                    <i class="bi bi-arrow-counterclockwise"></i> Restore to Default
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function restoreField(field) {
        if (!confirm('Restore "' + field + '" to its default value?')) return;

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('developer.branding.restore') }}';

        var csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        var fieldInput = document.createElement('input');
        fieldInput.type = 'hidden';
        fieldInput.name = 'field';
        fieldInput.value = field;
        form.appendChild(fieldInput);

        document.body.appendChild(form);
        form.submit();
    }

    function confirmRestoreAll() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Restore all to defaults?',
                text: 'This will clear all custom branding values and delete uploaded custom files.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, restore all',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
            }).then(function (result) {
                if (result.isConfirmed) {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('developer.branding.restore') }}';

                    var csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        } else {
            if (confirm('Restore all branding settings to default values? This cannot be undone.')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('developer.branding.restore') }}';

                var csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                document.body.appendChild(form);
                form.submit();
            }
        }
    }

    function updatePreview() {
        var sidebarPreview = document.getElementById('sidebar-preview');
        if (!sidebarPreview) return;

        var bg = document.getElementById('sidebar_background_color');
        if (bg) sidebarPreview.style.backgroundColor = bg.value;

        var brandText = document.getElementById('sidebar_brand_text_color');
        if (brandText) {
            var brandEls = sidebarPreview.querySelectorAll('.brand');
            brandEls.forEach(function (el) { el.style.color = brandText.value; });
        }

        var textColor = document.getElementById('sidebar_text_color');
        if (textColor) {
            var subtitleEls = sidebarPreview.querySelectorAll('.subtitle');
            subtitleEls.forEach(function (el) { el.style.color = textColor.value; });
            var navItems = sidebarPreview.querySelectorAll('.nav-item:not(.active):not(.hover)');
            navItems.forEach(function (el) { el.style.color = textColor.value; });
        }

        var activeColor = document.getElementById('sidebar_active_color');
        if (activeColor) {
            var activeEl = sidebarPreview.querySelector('.nav-item.active');
            if (activeEl) activeEl.style.backgroundColor = activeColor.value;
        }

        var hoverBg = document.getElementById('sidebar_hover_background_color');
        var hoverText = document.getElementById('sidebar_hover_text_color');
        if (hoverBg) {
            var hoverEl = sidebarPreview.querySelector('.nav-item.hover');
            if (hoverEl) {
                hoverEl.style.backgroundColor = hoverBg.value;
                if (hoverText) hoverEl.style.color = hoverText.value;
            }
        }

        var brandName = document.getElementById('sidebar_brand_name');
        var previewName = document.getElementById('preview-brand-name');
        if (brandName && previewName) previewName.textContent = brandName.value || 'Library System';

        var brandSubtitle = document.getElementById('sidebar_brand_subtitle');
        var previewSubtitle = document.getElementById('preview-brand-subtitle');
        if (brandSubtitle && previewSubtitle) previewSubtitle.textContent = brandSubtitle.value || 'Knowledge Gateway';
    }

    // Attach change listeners to all color inputs and text inputs
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.color-hex, input[type="color"], #sidebar_brand_name, #sidebar_brand_subtitle').forEach(function (el) {
            el.addEventListener('input', updatePreview);
        });
    });
</script>
@endpush

@include('components.contrast-warnings')