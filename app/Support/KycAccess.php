<?php

namespace App\Support;

use App\Models\User;

/**
 * Who may see a member's KYC photo and document scans.
 *
 * Follows the hierarchy, and nothing wider:
 *   - Super Admin / Admin: everyone.
 *   - A Sub Admin: the Branch Managers they own (BranchManagerAccess).
 *   - A Branch Manager: the Commission Partners they created.
 *   - Anyone: their own.
 */
final class KycAccess
{
    public static function canView(User $viewer, User $subject): bool
    {
        if ($viewer->id === $subject->id) {
            return true;
        }

        if ($viewer->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        if ($subject->hasRole('branch_manager') && $viewer->hasRole('sub_admin')) {
            return BranchManagerAccess::canManage($viewer) && BranchManagerAccess::owns($viewer, $subject);
        }

        if ($subject->hasRole('commission_partner') && $viewer->hasRole('branch_manager')) {
            return $subject->created_by === $viewer->id;
        }

        return false;
    }
}
