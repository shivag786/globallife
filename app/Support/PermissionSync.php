<?php

namespace App\Support;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Save a user's matrix permissions without tripping over a stale or
 * incomplete permission list.
 *
 * spatie resolves names against its cached permission list, so a permission
 * added straight in the database (an SQL import with no cache reset) or a
 * module whose migration has not run yet made the save fail with
 * PermissionDoesNotExist. Every name passed here has already been checked
 * against the matrix (PermissionMatrixService / BranchPermissionMatrixService),
 * so a missing row is created rather than refused.
 */
class PermissionSync
{
    /**
     * @param  list<string>  $names  matrix permission names, already validated
     */
    public static function sync(User $user, array $names): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        foreach ($names as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $user->syncPermissions(array_values($names));

        $registrar->forgetCachedPermissions();
    }
}
