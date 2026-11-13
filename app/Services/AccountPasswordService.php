<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * A user's own password: changing it while logged in, or resetting it through
 * the emailed forgot-password link. (Setting someone ELSE's password stays in
 * the per-role services — BranchManagerService, SubAdminService,
 * CommissionPartnerService — which also call AccountMailService.)
 *
 * Either way the old credentials are cut off: other stored sessions are dropped
 * and the remember-me token rotated, then a security notice is emailed.
 */
class AccountPasswordService
{
    public function __construct(private readonly AccountMailService $mail) {}

    /**
     * @param  string|null  $keepSessionId  the session to leave signed in (the
     *                                      one making the change); null drops all
     */
    public function setOwnPassword(User $user, string $password, ?string $keepSessionId = null): void
    {
        DB::transaction(function () use ($user, $password, $keepSessionId) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            // The session driver is `database`, so this is where a live login lives.
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->when($keepSessionId, fn ($q) => $q->where('id', '!=', $keepSessionId))
                ->delete();
        });

        $this->mail->passwordChanged($user);
    }
}
