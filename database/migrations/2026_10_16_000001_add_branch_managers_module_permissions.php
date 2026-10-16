<?php

use App\Services\PermissionMatrixService;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Register the `branch-managers` module in the Sub Admin permission matrix.
 *
 * Sub Admin now sits between Super Admin and Branch Manager in the user
 * hierarchy, and this module is what places it there: granted, a Sub Admin
 * creates and runs the Branch Managers they create.
 *
 * The seeder creates these too, but production runs migrations rather than
 * seeders, so without this the permissions screen would offer toggles that
 * cannot be saved — spatie refuses to grant a permission that has no row.
 */
return new class extends Migration
{
    public function up(): void
    {
        $permissions = PermissionMatrixService::permissionsForModule('branch-managers');

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Admin holds every content module by default (Super Admin bypasses the
        // matrix entirely via Gate::before), so keep it consistent here.
        Role::where('name', 'admin')->where('guard_name', 'web')->first()
            ?->givePermissionTo($permissions);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::whereIn('name', PermissionMatrixService::permissionsForModule('branch-managers'))
            ->where('guard_name', 'web')
            ->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
