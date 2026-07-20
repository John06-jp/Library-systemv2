@extends('layouts.sec')

@section('title', 'Developer Dashboard')

@section('styles')
<style>
    .branding-summary-card { max-width: 800px; }
    .branding-preview { max-height: 120px; object-fit: contain; border-radius: 6px; }
    .branding-preview-sm { max-height: 60px; object-fit: contain; }
    .status-badge-custom { background-color: #dbeafe; color: #1e40af; }
    .status-badge-original { background-color: #dcfce7; color: #166534; }
</style>
@endsection

@section('content')
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0">Developer Dashboard</h4>
            <p class="text-muted mb-0 small">System-wide branding overview</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card branding-summary-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Current Branding</span>
            <span class="badge {{ $customizedCount > 0 ? 'status-badge-custom' : 'status-badge-original' }} fs-6 px-3 py-1">
                {{ $customizedCount > 0 ? $customizedCount.' field(s) customized' : 'All defaults' }}
            </span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Sidebar Logo -->
                <div class="col-md-6">
                    <label class="fw-semibold small text-muted">Sidebar Logo</label>
                    <div>
                        <img src="{{ $branding['sidebar_logo_path'] ?? asset('images/logo.png') }}"
                             alt="Sidebar Logo" class="branding-preview-sm mt-1">
                    </div>
                </div>

                <!-- Banner -->
                <div class="col-md-6">
                    <label class="fw-semibold small text-muted">Application Banner</label>
                    <div>
                        <img src="{{ $branding['banner_path'] ?? asset('images/banner.png') }}"
                             alt="Banner" class="branding-preview mt-1">
                    </div>
                </div>

                <!-- Sidebar Brand Name -->
                <div class="col-md-6">
                    <label class="fw-semibold small text-muted">Sidebar Brand Name</label>
                    <div class="mt-1">{{ $branding['sidebar_brand_name'] ?? 'Library System' }}</div>
                </div>

                <!-- Sidebar Brand Subtitle -->
                <div class="col-md-6">
                    <label class="fw-semibold small text-muted">Sidebar Brand Subtitle</label>
                    <div class="mt-1">{{ $branding['sidebar_brand_subtitle'] ?? 'Knowledge Gateway' }}</div>
                </div>

                <!-- Last Updated -->
                <div class="col-12">
                    <hr class="my-1">
                    <div class="d-flex justify-content-between small text-muted">
                        <span>
                            Last updated:
                            @if($setting->updater)
                                {{ $setting->updater->name }}
                            @else
                                <em>Never</em>
                            @endif
                        </span>
                        <span>
                            @if($setting->updated_at)
                                {{ $setting->updated_at->format('M d, Y h:i A') }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection