<?php

namespace App\Services;

use App\Models\CommissionPartnerUpdate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CommissionPartnerService
{
    /**
     * Create a Commission Partner account owned by the given Branch Manager.
     *
     * Cities are not assigned here: a partner picks one up when they register a
     * VIP Member in it, so their territory follows the work they actually do.
     *
     * @param  array<string, mixed>  $data
     */
    public function createPartner(array $data, float $commissionPercentage, User $branchManager): User
    {
        return DB::transaction(function () use ($data, $commissionPercentage, $branchManager) {
            $partner = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => 'active',
                'commission_percentage' => $commissionPercentage,
                'created_by' => $branchManager->id,
                'email_verified_at' => now(),
            ]);

            $partner->assignRole('commission_partner');

            return $partner;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    /**
     * Super Admin changing a Commission Partner's details.
     *
     * The partner is managed by their Branch Manager, so a change made over their
     * head is recorded as a note addressed to that Branch Manager: which fields
     * changed, from what to what, who did it and why. The update and the note are
     * one transaction, so a Branch Manager never sees a change without its note.
     *
     * Returns the note, or null when nothing actually changed and no reason was
     * given -- an empty save should not tell the Branch Manager anything.
     *
     * @param  array{name: string, email: string, mobile?: ?string, commission_percentage: float|int|string, note?: ?string}  $data
     */
    public function adminUpdatePartner(User $partner, array $data, User $admin): ?CommissionPartnerUpdate
    {
        return DB::transaction(function () use ($partner, $data, $admin) {
            $incoming = [
                'name' => $data['name'],
                'email' => $data['email'],
                'mobile' => $data['mobile'] ?? null,
                'commission_percentage' => (float) $data['commission_percentage'],
            ];

            $changes = [];

            foreach ($incoming as $field => $new) {
                $old = $partner->{$field};

                $differs = $field === 'commission_percentage'
                    ? round((float) $old, 2) !== round((float) $new, 2) || ($old === null) !== ($new === null)
                    : (string) $old !== (string) $new;

                if ($differs) {
                    $changes[$field] = [$old, $new];
                }
            }

            $note = isset($data['note']) && trim((string) $data['note']) !== '' ? trim($data['note']) : null;

            if ($changes === [] && $note === null) {
                return null;
            }

            if ($changes !== []) {
                $partner->update(array_intersect_key($incoming, $changes));
            }

            return CommissionPartnerUpdate::create([
                'commission_partner_id' => $partner->id,
                'branch_manager_id' => $partner->created_by,
                'updated_by' => $admin->id,
                'changed_fields' => $changes,
                'note' => $note,
            ]);
        });
    }

    /**
     * A Branch Manager acknowledging Super Admin's notes about their partners.
     * Only their own notes are touched.
     *
     * @param  list<int>|null  $ids  null marks every unread note as read
     */
    public function markUpdatesRead(User $branchManager, ?array $ids = null): int
    {
        return CommissionPartnerUpdate::query()
            ->where('branch_manager_id', $branchManager->id)
            ->unread()
            ->when($ids !== null, fn ($q) => $q->whereIn('id', $ids))
            ->update(['read_at' => now()]);
    }

    public function updatePartner(User $partner, array $data, float $commissionPercentage): User
    {
        return DB::transaction(function () use ($partner, $data, $commissionPercentage) {
            $partner->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'commission_percentage' => $commissionPercentage,
            ]);

            return $partner;
        });
    }

    /**
     * Set a Commission Partner's password on their Branch Manager's behalf.
     *
     * A reset usually answers a lost or compromised login, so the old
     * credentials are cut off properly: every stored session for that account is
     * dropped and the remember-me token rotated, or an open browser would keep
     * working with the password that was just replaced.
     */
    public function setPassword(User $partner, string $password): User
    {
        return DB::transaction(function () use ($partner, $password) {
            $partner->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            // The session driver is `database`, so this is where a live login lives.
            DB::table('sessions')->where('user_id', $partner->id)->delete();

            return $partner;
        });
    }

    public function toggleStatus(User $partner): User
    {
        $partner->update(['status' => $partner->status === 'active' ? 'blocked' : 'active']);

        return $partner;
    }
}
