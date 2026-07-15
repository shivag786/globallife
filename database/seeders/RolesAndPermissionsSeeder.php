<?php

namespace Database\Seeders;

use App\Services\BranchPermissionMatrixService;
use App\Services\PermissionMatrixService;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * The 7 roles defined by the platform's RBAC model.
     *
     * @var list<string>
     */
    public const ROLES = ['super_admin', 'admin', 'sub_admin', 'branch_manager', 'commission_partner', 'vip_member', 'customer', 'visitor'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::ROLES as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        foreach (PermissionMatrixService::allPermissions() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (BranchPermissionMatrixService::allPermissions() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Admin manages every content module by default (Super Admin bypasses the matrix entirely).
        Role::findByName('admin')->syncPermissions(PermissionMatrixService::allPermissions());

        // Sub Admin starts with no permissions; Super Admin grants module-scoped access per user.
        // Branch Manager starts with no branch.* permissions; Super Admin grants them per-user
        // (direct-to-user permissions, not role-level — see BranchManagerController::updatePermissions()).
        // Commission Partner / VIP Member / Visitor operate through dedicated, scoped features
        // rather than any permission matrix, so they are left without matrix permissions.
    }
}
