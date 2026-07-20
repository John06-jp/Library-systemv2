<div class="data-panel-table-wrap">
    @if($users->total() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        @php
                            $roleClass = in_array($user->role, ['admin', 'staff', 'faculty', 'student'], true)
                                ? $user->role
                                : 'default';
                        @endphp
                        <tr>
                            <td>
                                <div class="accounts-user-cell">
                                    <strong>{{ $user->fname }} {{ $user->lname }}</strong>
                                    <small>ID #{{ $user->id }}</small>
                                </div>
                            </td>
                            <td>
                                <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                            </td>
                            <td>
                                <span class="accounts-badge accounts-badge--{{ $roleClass }}">{{ $user->role }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('users.edit', $user->id) }}"
                                   class="accounts-btn accounts-btn--warning accounts-btn--sm">Edit</a>
                                @if((int) $user->id !== (int) auth()->id())
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this user account?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="accounts-btn accounts-btn--danger accounts-btn--sm">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('layouts.partials.pagination_bar', ['paginator' => $users])
    @else
        <div class="accounts-empty">
            <div class="accounts-empty__icon">👤</div>
            <p class="mb-2">No user accounts yet.</p>
            <a href="{{ route('users.create') }}" class="accounts-btn accounts-btn--primary">Create first account</a>
        </div>
    @endif
</div>
