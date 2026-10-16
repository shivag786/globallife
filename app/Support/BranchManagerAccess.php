<?php

namespace App\Support;

use App\Models\User;
use App\Services\PermissionMatrixService;
use Illuminate\Database\Eloquent\Builder;

/**
 * Who may manage which Branch Managers.
 *
 * The user hierarchy is Super Admin → Sub Admin → Branch Manager → Commission
 * Partner → VIP Member. A Sub Admin granted the `branch-managers` module runs
 * the Branch Managers they created and nobody else's; Super Admin sees every
 * one, including the ones created before Sub Admins existed (`created_by` NULL).
 *
 * Kept in one place because the form requests and the controller have to agree:
 * a scoped list that an unscoped update endpoint sat behind would be decoration.
 */
final class BranchManagerAccess
{
    public const MODULE = 'branch-managers';

    /**
     * Whether this account may open the Branch Managers module at all.
     *
     * Goes through PermissionMatrixService (and so through Gate), which honours
     * the Super Admin bypass without naming the role.
     */
    public static function canManage(User $actor): bool
    {
        return PermissionMatrixService::userCanAccessModule($actor, self::MODULE);
    }

    /**
     * Whether this account sees every Branch Manager rather than just its own.
     */
    public static function seesAll(User $actor): bool
    {
        // Granting the module is not the same as owning the whole platform: only
        // an account that passes the Super Admin gate sees other people's.
        return $actor->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Whether this account may act on one specific Branch Manager.
     */
    public static function owns(User $actor, User $manager): bool
    {
        if (! $manager->hasRole('branch_manager')) {
            return false;
        }

        return self::seesAll($actor) || $manager->created_by === $actor->id;
    }

    /**
     * Narrow a Branch Manager query to what this account is allowed to see.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public static function scope(Builder $query, User $actor): Builder
    {
        if (self::seesAll($actor)) {
            return $query;
        }

        return $query->where('created_by', $actor->id);
    }
}
