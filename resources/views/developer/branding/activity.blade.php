@extends('layouts.sec')

@section('title', 'Branding Activity Log')

@section('styles')
<style>
    .activity-table { font-size: 0.9rem; }
    .badge-branding { background-color: #818cf8; color: #fff; }
</style>
@endsection

@section('content')
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0">Branding Activity Log</h4>
            <p class="text-muted mb-0 small">Audit trail of all branding changes</p>
        </div>
    </div>

    @if($activities->count() === 0)
        <div class="alert alert-info">No branding activity recorded yet.</div>
    @else
        <div class="table-responsive">
            <table class="table table-hover activity-table">
                <thead class="table-light">
                    <tr>
                        <th>Date & Time</th>
                        <th>Developer</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                    <tr>
                        <td class="text-nowrap">{{ $activity->created_at->format('M d, Y h:i A') }}</td>
                        <td>{{ $activity->user?->name ?? 'Unknown' }}</td>
                        <td>
                            @php
                                $badgeClass = match (true) {
                                    str_contains($activity->type, 'updated') => 'bg-primary',
                                    str_contains($activity->type, 'restored') => 'bg-warning text-dark',
                                    str_contains($activity->type, 'version') => 'bg-info',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $activity->title }}</span>
                        </td>
                        <td>{{ $activity->body ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">
            {{ $activities->links() }}
        </div>
    @endif
</div>
@endsection