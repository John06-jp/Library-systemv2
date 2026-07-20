@extends('layouts.sec')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/accounts/accounts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout/skeleton.css') }}">
@endsection

@section('content')
<div class="accounts-page">
    <header class="accounts-page__hero">
        <div>
            <p class="accounts-page__eyebrow">User management</p>
            <h1 class="accounts-page__title">User accounts</h1>
            <p class="accounts-page__subtitle">Manage logins for staff, faculty, and administrators.</p>
        </div>
        <div class="accounts-page__hero-actions">
            <a href="{{ route('users.create') }}" class="accounts-btn accounts-btn--primary">+ Create account</a>
            <a href="{{ route('book.index') }}" class="accounts-btn accounts-btn--outline">← Catalog</a>
        </div>
    </header>

    @include('accounts.partials.subnav')
    @include('accounts.partials.alerts')

    <div class="accounts-stats">
        <div class="accounts-stat">
            <div class="accounts-stat__value">{{ $users->total() }}</div>
            <div class="accounts-stat__label">Total users</div>
        </div>
        <div class="accounts-stat">
            <div class="accounts-stat__value">{{ $roleCounts->get('admin', 0) }}</div>
            <div class="accounts-stat__label">Admins</div>
        </div>
        <div class="accounts-stat">
            <div class="accounts-stat__value">{{ $roleCounts->get('staff', 0) }}</div>
            <div class="accounts-stat__label">Staff</div>
        </div>
        <div class="accounts-stat">
            <div class="accounts-stat__value">{{ $roleCounts->get('faculty', 0) + $roleCounts->get('student', 0) }}</div>
            <div class="accounts-stat__label">Faculty / Student</div>
        </div>
    </div>

    <form id="users-filter-form" method="GET" action="{{ route('users.index') }}" class="d-none" aria-hidden="true"></form>

    <div class="accounts-card accounts-card--flush-table">
        <div id="users-data-panel"
             data-hydratable-panel
             data-loading="false"
             data-form="#users-filter-form"
             data-skeleton="#users-table-skeleton"
             data-pagination=".data-panel-pagination"
             data-path-match="/view-users">
            @include('accounts.partials.list-table', ['users' => $users])
        </div>
    </div>
</div>

<template id="users-table-skeleton">
    @include('partials.skeleton-table', [
        'columns' => 4,
        'rows' => 8,
        'loadingLabel' => 'Loading user accounts…',
        'headers' => ['Name', 'Email', 'Role', 'Actions'],
        'skeletonFirstCol' => 'text',
    ])
</template>
@endsection
