<?php

namespace App\Services;

use App\Models\User;

class PermissionMatrixService
{
    /**
     * Modules a Sub Admin's access can be scoped to.
     *
     * `branch-managers` is the one that is not content: it puts the Sub Admin into
     * the user hierarchy, letting them run the Branch Managers they create.
     *
     * @var list<string>
     */
    public const MODULES = [
        'blog', 'products', 'leads', 'events', 'media', 'testimonials', 'branch-managers',
        // Employee Management (HR). Company-wide, not part of the user hierarchy.
        'employees', 'attendance', 'leaves', 'payroll', 'assets', 'expenses',
    ];

    /**
     * The HR modules of the matrix, in sidebar order.
     *
     * @var list<string>
     */
    public const HR_MODULES = ['employees', 'attendance', 'leaves', 'payroll', 'assets', 'expenses'];

    /**
     * Actions assignable per module.
     *
     * @var list<string>
     */
    public const ACTIONS = ['create', 'edit', 'delete', 'publish', 'approve', 'export'];

    /**
     * Modules where only some of ACTIONS mean anything.
     *
     * Managing accounts has nothing to publish, approve or export, and offering
     * those toggles would grant permissions no code ever checks. Anything absent
     * here takes the full ACTIONS list.
     *
     * @var array<string, list<string>>
     */
    private const MODULE_ACTIONS = [
        'branch-managers' => ['create', 'edit', 'delete'],
        // `employees.edit` also covers the HR masters (departments, shifts,
        // holidays, leave types, announcements).
        'employees' => ['create', 'edit', 'export'],
        'attendance' => ['edit', 'approve', 'export'],
        'leaves' => ['approve'],
        // `create` generates a draft payroll, `approve` finalises it.
        'payroll' => ['create', 'approve', 'export'],
        'assets' => ['create', 'edit', 'delete'],
        'expenses' => ['approve'],
    ];

    /**
     * The actions worth offering for one module.
     *
     * @return list<string>
     */
    public static function actionsForModule(string $module): array
    {
        return self::MODULE_ACTIONS[$module] ?? self::ACTIONS;
    }

    /**
     * All "{module}.{action}" permission names in the matrix.
     *
     * @return list<string>
     */
    public static function allPermissions(): array
    {
        $permissions = [];

        foreach (self::MODULES as $module) {
            foreach (self::actionsForModule($module) as $action) {
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
        return array_map(fn (string $action) => "{$module}.{$action}", self::actionsForModule($module));
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
