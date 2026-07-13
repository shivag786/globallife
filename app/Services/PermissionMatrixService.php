<?php

namespace App\Services;

use App\Models\User;

class PermissionMatrixService
{
    /**
     * Content modules a Sub Admin's access can be scoped to.
     *
     * @var list<string>
     */
    public const MODULES = ['blog', 'products', 'leads', 'events', 'media', 'testimonials'];

    /**
     * Actions assignable per module.
     *
     * @var list<string>
     */
    public const ACTIONS = ['create', 'edit', 'delete', 'publish', 'approve', 'export'];

    /**
     * All "{module}.{action}" permission names in the matrix.
     *
     * @return list<string>
     */
    public static function allPermissions(): array
    {
        $permissions = [];

        foreach (self::MODULES as $module) {
            foreach (self::ACTIONS as $action) {
                $permissions[] = "{$module}.{$action}";
            }
        }

        return $permissions;
    }

    /**
     * Permission names for a single module, e.g. "blog" => ["blog.create", "blog.edit", ...].
     *
     * @return list<string>
     */
    public static function permissionsForModule(string $module): array
    {
        return array_map(fn (string $action) => "{$module}.{$action}", self::ACTIONS);
    }

    /**
     * Whether the user can perform at least one action in the given module.
     *
     * Goes through Gate (User::can()) rather than spatie's hasAnyPermission(), so the
     * Super Admin Gate::before bypass in AppServiceProvider is honored.
     */
    public static function userCanAccessModule(User $user, string $module): bool
    {
        foreach (self::permissionsForModule($module) as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Group a flat permission name list back into module => [actions].
     *
     * @param  iterable<string>  $permissionNames
     * @return array<string, list<string>>
     */
    public static function groupByModule(iterable $permissionNames): array
    {
        $grouped = array_fill_keys(self::MODULES, []);

        foreach ($permissionNames as $name) {
            [$module, $action] = explode('.', $name, 2);

            if (array_key_exists($module, $grouped)) {
                $grouped[$module][] = $action;
            }
        }

        return $grouped;
    }
}
