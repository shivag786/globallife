<?php

namespace App\Support;

use App\Models\User;
use App\Services\PermissionMatrixService;
use Illuminate\Support\Facades\Auth;

/**
 * Gatekeeping for the admin HR screens.
 *
 * The admin route group lets every Sub Admin through; the HR modules of the
 * permission matrix are what actually open these pages. Admin holds every
 * module through its role and Super Admin passes via Gate::before.
 */
class HrAccess
{
    /** Any action in the module opens its pages. */
    public static function module(string $module): User
    {
        $user = Auth::user();

        abort_unless(PermissionMatrixService::userCanAccessModule($user, $module), 403);

        return $user;
    }

    /** A specific `{module}.{action}` permission. */
    public static function ability(string $permission): User
    {
        $user = Auth::user();

        abort_unless($user->can($permission), 403);

        return $user;
    }

    public static function any(User $user): bool
    {
        foreach (PermissionMatrixService::HR_MODULES as $module) {
            if (PermissionMatrixService::userCanAccessModule($user, $module)) {
                return true;
            }
        }

        return false;
    }
}
