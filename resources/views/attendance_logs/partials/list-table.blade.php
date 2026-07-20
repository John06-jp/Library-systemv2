<div class="data-panel-table-wrap">
    @if($logs->total() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Program</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th>Scanned at</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        @php
                            $student = $log->student;
                            $programLabel = $student
                                ? ($programs->firstWhere('program_code', $student->course)?->program_name ?? $student->course)
                                : null;
                            $status = strtolower((string) $log->status);
                            $scanned = $log->scanned_at?->timezone('Asia/Manila');
                        @endphp
                        <tr>
                            <td>
                                @if($student)
                                    <div class="attn-logs__person-name">
                                        {{ $student->lastname }}, {{ $student->firstname }}
                                    </div>
                                    @if($student->id_number)
                                        <div class="attn-logs__person-meta">ID {{ $student->id_number }}</div>
                                    @endif
                                @else
                                    <span class="text-muted">Unknown student</span>
                                @endif
                            </td>
                            <td>
                                @if($programLabel)
                                    <span class="attn-logs__chip">{{ $programLabel }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($student?->year)
                                    <span class="attn-logs__chip attn-logs__chip--muted">{{ $student->year }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($status === 'in')
                                    <span class="attn-logs__status attn-logs__status--in">In</span>
                                @elseif($status === 'out')
                                    <span class="attn-logs__status attn-logs__status--out">Out</span>
                                @else
                                    <span class="attn-logs__status attn-logs__status--unknown">{{ $log->status ?? '—' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($scanned)
                                    <span class="attn-logs__time">
                                        {{ $scanned->format('M j, Y') }}
                                        <small>{{ $scanned->format('g:i A') }}</small>
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('layouts.partials.pagination_bar', ['paginator' => $logs])
    @else
        <div class="attn-logs__empty">
            <p class="mb-2">No attendance records match your filters.</p>
            <a href="{{ route('attendance_logs.index') }}" class="attn-logs__btn attn-logs__btn--outline">Clear filters</a>
        </div>
    @endif
</div>
