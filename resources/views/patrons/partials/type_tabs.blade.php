<nav class="patron-dir__tabs" aria-label="Patron type">
    <a href="{{ route('students.index') }}"
       class="patron-dir__tab {{ ($active ?? '') === 'students' ? 'active' : '' }}">
        Students
    </a>
    <a href="{{ route('employees.index') }}"
       class="patron-dir__tab {{ ($active ?? '') === 'employees' ? 'active' : '' }}">
        Faculty &amp; Staff
    </a>
</nav>
