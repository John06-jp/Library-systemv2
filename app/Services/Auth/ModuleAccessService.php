<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class ModuleAccessService
{
    public function isDeveloper(?Authenticatable $user): bool
    {
        return $user instanceof User && $user->hasRole('developer');
    }

    public function hasLibraryAccess(?Authenticatable $user): bool
    {
        return $user instanceof User && $user->hasAnyRole(['admin', 'staff']);
    }

    public function hasAttendanceAccess(?Authenticatable $user): bool
    {
        return false;
    }
}
