<a href="{{ route('attendance_logs.index') }}">Attendance Logs</a>
@can('isAdmin')
<a href="{{ route('admin.attendance.feedbacks') }}">Gate Feedback Responses</a>
<a href="{{ route('fines.outstanding') }}">Outstanding Fines</a>
@endcan
<a href="{{ route('reports.library_holdings.create') }}">Library Holdings Report</a>
<a href="{{ route('book.report.download') }}">Download Book Report (PDF)</a>
<a href="{{ route('feedback.index') }}">Student Feedback</a>
<a href="{{ route('admin.activities.index') }}">Activity log</a>
@can('isAdmin')
<a href="{{ route('rooms.logs') }}">Reservation Logs</a>
@endcan
