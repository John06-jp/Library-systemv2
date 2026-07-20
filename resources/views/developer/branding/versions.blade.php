@extends('layouts.sec')

@section('title', 'Branding Version History')

@section('styles')
<style>
    .versions-table { font-size: 0.9rem; }
    .snapshot-fields { font-size: 0.8rem; color: #64748b; max-width: 300px; }
</style>
@endsection

@section('content')
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0">Branding Version History</h4>
            <p class="text-muted mb-0 small">Snapshot history of branding changes</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($versions->count() === 0)
        <div class="alert alert-info">No version snapshots yet. Snapshots are created automatically whenever branding is updated.</div>
    @else
        <div class="table-responsive">
            <table class="table table-hover versions-table">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Date & Time</th>
                        <th>Changed By</th>
                        <th>Fields Included</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($versions as $version)
                    <tr>
                        <td>{{ $version->getKey() }}</td>
                        <td class="text-nowrap">{{ $version->created_at->format('M d, Y h:i A') }}</td>
                        <td>{{ $version->changer?->name ?? 'Unknown' }}</td>
                        <td class="snapshot-fields">
                            @php
                                $fields = array_keys($version->snapshot ?? []);
                                $nonNullFields = [];
                                foreach ($version->snapshot as $key => $value) {
                                    if ($value !== null) {
                                        $label = str_replace('_', ' ', $key);
                                        $nonNullFields[] = $label;
                                    }
                                }
                            @endphp
                            @if(count($nonNullFields) > 0)
                                {{ implode(', ', array_slice($nonNullFields, 0, 5)) }}
                                @if(count($nonNullFields) > 5)
                                    , and {{ count($nonNullFields) - 5 }} more
                                @endif
                            @else
                                <em>No customized fields</em>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('developer.branding.restore-version', $version) }}"
                                  onsubmit="return confirm('Restore branding to version #{{ $version->getKey() }} from {{ $version->created_at->format('M d, Y h:i A') }}? This will create a new snapshot of the current state.');">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning btn-sm">Restore</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">
            {{ $versions->links() }}
        </div>
    @endif
</div>
@endsection