<a href="{{ route('logs.index') }}">Circulation</a>
<a href="{{ route('catalog.copy.openlibrary.form') }}">Copy Cataloging</a>
<a href="{{ route('rfid.scanner') }}" hidden>RFID Scanner</a>
@can('isAdmin')
<a href="{{ route('circulation.policy.edit') }}">Circulation Policy</a>
@endcan
