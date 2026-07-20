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
    .live-preview-shell { overflow: hidden; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff; box-shadow: 0 8px 24px rgba(15, 23, 42, .08); }
    .live-preview-banner { height: 90px; width: 100%; object-fit: cover; background: #e2e8f0; }
    .live-preview-layout { display: grid; grid-template-columns: minmax(170px, 32%) 1fr; min-height: 330px; }
    .live-preview-sidebar { display: flex; flex-direction: column; padding: 1rem 0 0; }
    .live-preview-brand { display: flex; gap: .65rem; align-items: center; padding: 0 .85rem 1rem; }
    .live-preview-logo { width: 42px; height: 42px; object-fit: contain; border-radius: 6px; background: rgba(255, 255, 255, .12); }
    .live-preview-nav { padding: 0 .65rem; flex: 1; }
    .live-preview-footer { margin-top: 1rem; padding: .75rem; font-size: .75rem; }
    .live-preview-content { padding: 1rem; min-width: 0; }
    .live-preview-table { width: 100%; border-collapse: collapse; font-size: .76rem; }
    .live-preview-table th, .live-preview-table td { padding: .55rem; border: 1px solid; }
    .live-preview-table tbody tr:nth-child(2) { transition: background-color .15s ease; }
    .live-preview-button { border: 0; border-radius: 6px; color: #fff; padding: .45rem .8rem; font-size: .78rem; }
    .live-preview-status { display: inline-flex; align-items: center; gap: .35rem; color: #64748b; font-size: .78rem; }
    .live-preview-status::before { content: ''; width: 7px; height: 7px; border-radius: 999px; background: #22c55e; }
    .opac-gradient-preview { min-height: 150px; display: grid; place-items: center; padding: 1.5rem; border-radius: 10px; color: #fff; text-align: center; box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .18); }
    .opac-gradient-preview h6 { color: inherit; font-weight: 700; margin-bottom: .35rem; }
    .opac-gradient-preview p { margin: 0; color: rgba(255, 255, 255, .85); font-size: .85rem; }
    .asset-preview-pending { outline: 3px solid #f59e0b; outline-offset: 2px; }
    @media (max-width: 767.98px) {
        .live-preview-layout { grid-template-columns: 1fr; }
        .live-preview-sidebar { min-height: 280px; }
    }
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
                        <img id="preview-asset-banner_path" src="{{ app(App\Services\BrandingService::class)->assetUrl('banner_path') }}" alt="Current Banner" class="preview-img-banner w-100">
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

        {{-- === OPAC LOGO SECTION === --}}
        <div class="branding-section">
            <h5>OPAC Library Logo</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="fw-semibold small text-muted">Current OPAC Logo</label>
                    <div class="preview-box mt-1">
                        <img id="preview-asset-opac_logo_path" src="{{ app(App\Services\BrandingService::class)->assetUrl('opac_logo_path') }}" alt="Current OPAC Logo" class="preview-img">
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
                        <img id="preview-asset-opac_default_book_cover_path" src="{{ app(App\Services\BrandingService::class)->assetUrl('opac_default_book_cover_path') }}" alt="Current Book Cover" class="preview-img">
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
                        <img id="preview-asset-sidebar_logo_path" src="{{ app(App\Services\BrandingService::class)->assetUrl('sidebar_logo_path') }}" alt="Current Sidebar Logo" class="preview-img">
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

            <div class="mb-4">
                <div class="fw-semibold mb-2">OPAC Gradient Preview</div>
                <div id="opac-gradient-preview" class="opac-gradient-preview"
                     style="background: linear-gradient(160deg, {{ $current['opac_gradient_start_color'] ?? '#2E7D32' }}, {{ $current['opac_gradient_end_color'] ?? '#1B5E20' }});">
                    <div>
                        <div class="small text-uppercase fw-semibold mb-1" style="letter-spacing:.12em;">Online Public Access Catalog</div>
                        <h6>Find books in our library</h6>
                        <p>Search by title, author, or keywords.</p>
                    </div>
                </div>
            </div>

            {{-- Live Preview Mockup --}}
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <div class="fw-semibold">Live Preview</div>
                    <div class="live-preview-status">Updates instantly — save to publish</div>
                </div>
                <span class="badge bg-warning-subtle text-warning-emphasis">Unsaved preview</span>
            </div>
            <div class="live-preview-shell mb-4" id="branding-live-preview">
                <img id="live-preview-banner"
                     src="{{ app(App\Services\BrandingService::class)->assetUrl('banner_path') }}"
                     alt="Application banner preview"
                     class="live-preview-banner">
                <div class="live-preview-layout">
                    <aside id="sidebar-preview" class="sidebar-mockup live-preview-sidebar p-0 rounded-0"
                           style="background-color: {{ $current['sidebar_background_color'] ?? '#1E293B' }};">
                        <div class="live-preview-brand brand" style="color: {{ $current['sidebar_brand_text_color'] ?? '#FFFFFF' }};">
                            <img id="live-preview-sidebar-logo"
                                 src="{{ app(App\Services\BrandingService::class)->assetUrl('sidebar_logo_path') }}"
                                 alt="Sidebar logo preview"
                                 class="live-preview-logo">
                            <div class="min-w-0">
                                <div id="preview-brand-name">{{ $current['sidebar_brand_name'] ?? 'Library System' }}</div>
                                <div id="preview-brand-subtitle" class="subtitle text-truncate"
                                     style="color: {{ $current['sidebar_text_color'] ?? '#CBD5E1' }};">
                                    {{ $current['sidebar_brand_subtitle'] ?? 'Knowledge Gateway' }}
                                </div>
                            </div>
                        </div>
                        <div class="live-preview-nav">
                            <div class="nav-item active">Dashboard</div>
                            <div class="nav-item hover">Branding</div>
                            <div class="nav-item standard">Version History</div>
                        </div>
                        <div id="live-preview-footer" class="live-preview-footer">Developer Console</div>
                    </aside>
                    <main class="live-preview-content">
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                            <div>
                                <div id="live-preview-heading" class="fw-bold">Branding overview</div>
                                <div id="live-preview-secondary" class="small">Preview of system components</div>
                            </div>
                            <button id="live-preview-button" type="button" class="live-preview-button">Primary action</button>
                        </div>
                        <div id="live-preview-accent" class="rounded p-2 mb-3 small">Accent notification</div>
                        <div class="table-responsive">
                            <table id="live-preview-table" class="live-preview-table">
                                <thead><tr><th>Asset</th><th>Status</th><th>Updated</th></tr></thead>
                                <tbody>
                                    <tr><td>Banner</td><td>Active</td><td>Today</td></tr>
                                    <tr id="live-preview-table-hover"><td>Sidebar logo</td><td>Active</td><td>Today</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </main>
                </div>
            </div>

            <div class="row g-3 mb-3 d-none" aria-hidden="true">
                <div class="col-md-6">
                    <div class="sidebar-mockup" id="legacy-sidebar-preview" style="background-color: {{ $current['sidebar_background_color'] ?? '#1E293B' }};">
                        <div class="brand" style="color: {{ $current['sidebar_brand_text_color'] ?? '#FFFFFF' }};">
                            <span id="legacy-preview-brand-name">{{ $current['sidebar_brand_name'] ?? 'Library System' }}</span>
                            <div class="subtitle" style="color: {{ $current['sidebar_text_color'] ?? '#CBD5E1' }};">
                                <span id="legacy-preview-brand-subtitle">{{ $current['sidebar_brand_subtitle'] ?? 'Knowledge Gateway' }}</span>
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
                    'opac_gradient_start_color' => 'OPAC Gradient Start',
                    'opac_gradient_end_color' => 'OPAC Gradient End',
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

        function color(field, fallback) {
            var input = document.getElementById(field);
            return input && /^#[0-9A-Fa-f]{6}$/.test(input.value) ? input.value : fallback;
        }

        var colors = {
            primary: color('primary_color', '#2563EB'),
            secondary: color('secondary_color', '#475569'),
            accent: color('accent_color', '#F59E0B'),
            opacGradientStart: color('opac_gradient_start_color', '#2E7D32'),
            opacGradientEnd: color('opac_gradient_end_color', '#1B5E20'),
            sidebarBg: color('sidebar_background_color', '#1E293B'),
            sidebarText: color('sidebar_text_color', '#CBD5E1'),
            brandText: color('sidebar_brand_text_color', '#FFFFFF'),
            active: color('sidebar_active_color', '#3B82F6'),
            hoverBg: color('sidebar_hover_background_color', '#334155'),
            hoverText: color('sidebar_hover_text_color', '#F8FAFC'),
            button: color('button_color', '#2563EB'),
            footer: color('sidebar_footer_background_color', '#0F172A'),
            tableHeader: color('table_header_color', '#1E293B'),
            tableHeaderText: color('table_header_text_color', '#F8FAFC'),
            tableBorder: color('table_border_color', '#E2E8F0'),
            tableHover: color('table_hover_color', '#F1F5F9')
        };

        sidebarPreview.style.backgroundColor = colors.sidebarBg;
        sidebarPreview.querySelector('.brand').style.color = colors.brandText;
        sidebarPreview.querySelector('.subtitle').style.color = colors.sidebarText;
        sidebarPreview.querySelector('.nav-item.standard').style.color = colors.sidebarText;
        sidebarPreview.querySelector('.nav-item.active').style.backgroundColor = colors.active;
        sidebarPreview.querySelector('.nav-item.hover').style.backgroundColor = colors.hoverBg;
        sidebarPreview.querySelector('.nav-item.hover').style.color = colors.hoverText;

        var footer = document.getElementById('live-preview-footer');
        footer.style.backgroundColor = colors.footer;
        footer.style.color = colors.sidebarText;

        document.getElementById('live-preview-heading').style.color = colors.primary;
        document.getElementById('live-preview-secondary').style.color = colors.secondary;
        document.getElementById('live-preview-button').style.backgroundColor = colors.button;

        var accent = document.getElementById('live-preview-accent');
        accent.style.borderLeft = '4px solid ' + colors.accent;
        accent.style.backgroundColor = colors.accent + '20';
        accent.style.color = colors.secondary;

        document.getElementById('opac-gradient-preview').style.background =
            'linear-gradient(160deg, ' + colors.opacGradientStart + ', ' + colors.opacGradientEnd + ')';

        var table = document.getElementById('live-preview-table');
        table.querySelector('thead').style.backgroundColor = colors.tableHeader;
        table.querySelector('thead').style.color = colors.tableHeaderText;
        table.querySelectorAll('th, td').forEach(function (cell) {
            cell.style.borderColor = colors.tableBorder;
        });
        document.getElementById('live-preview-table-hover').style.backgroundColor = colors.tableHover;

        var brandName = document.getElementById('sidebar_brand_name');
        var previewName = document.getElementById('preview-brand-name');
        if (brandName && previewName) previewName.textContent = brandName.value || 'Library System';

        var brandSubtitle = document.getElementById('sidebar_brand_subtitle');
        var previewSubtitle = document.getElementById('preview-brand-subtitle');
        if (brandSubtitle && previewSubtitle) previewSubtitle.textContent = brandSubtitle.value || 'Knowledge Gateway';

        document.querySelectorAll('.color-hex').forEach(function (input) {
            var picker = document.getElementById(input.id + '-picker');
            var swatch = picker ? picker.closest('.input-group-text') : null;
            var valid = /^#[0-9A-Fa-f]{6}$/.test(input.value);
            if (picker && valid) picker.value = input.value;
            if (swatch && valid) swatch.style.backgroundColor = input.value;
        });
    }

    function previewUploadedAsset(input) {
        var file = input.files && input.files[0];
        if (!file || !file.type.startsWith('image/')) return;

        if (input.dataset.previewUrl) URL.revokeObjectURL(input.dataset.previewUrl);
        var objectUrl = URL.createObjectURL(file);
        input.dataset.previewUrl = objectUrl;

        var sectionPreview = document.getElementById('preview-asset-' + input.id);
        if (sectionPreview) {
            sectionPreview.src = objectUrl;
            sectionPreview.classList.add('asset-preview-pending');
        }

        if (input.id === 'banner_path') {
            document.getElementById('live-preview-banner').src = objectUrl;
        }
        if (input.id === 'sidebar_logo_path') {
            document.getElementById('live-preview-sidebar-logo').src = objectUrl;
        }
    }

    // Attach change listeners to all previewable inputs.
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.color-hex, input[type="color"], #sidebar_brand_name, #sidebar_brand_subtitle').forEach(function (el) {
            el.addEventListener('input', updatePreview);
        });

        ['banner_path', 'opac_logo_path', 'opac_default_book_cover_path', 'sidebar_logo_path'].forEach(function (field) {
            var input = document.getElementById(field);
            if (input) input.addEventListener('change', function () { previewUploadedAsset(input); });
        });

        updatePreview();
    });
</script>
@endpush

@include('components.contrast-warnings')
