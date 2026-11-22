<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AccountCreatedNotification;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Account emails that are informational: a failed send is logged and swallowed,
 * never allowed to undo or block the action that triggered it (an account was
 * still created, a password was still changed).
 *
 * The forgot-password link is the exception and does NOT go through here — it is
 * the whole point of that request, so its failure is surfaced to the user (see
 * User::sendPasswordResetNotification()).
 */
class AccountMailService
{
    /**
     * Welcome email with the login email and password.
     *
     * @param  string|null  $password  the plain password the account was created with
     */
    public function accountCreated(User $user, ?User $createdBy = null, ?string $password = null): void
    {
        $this->safely($user, new AccountCreatedNotification($createdBy?->name, $password));
    }

    /**
     * @param  string|null  $changedBy  who changed it, when it was not the user
     *                                  themselves (e.g. "your Branch Manager")
     */
    public function passwordChanged(User $user, ?string $changedBy = null): void
    {
        $this->safely($user, new PasswordChangedNotification($changedBy));
    }

    private function safely(User $user, object $notification): void
    {
        if (empty($user->email)) {
            return;
        }

        try {
            $user->notify($notification);
        } catch (Throwable $e) {
            Log::warning('Account email could not be sent', [
                'user_id' => $user->id,
                'notification' => $notification::class,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
