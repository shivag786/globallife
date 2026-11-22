<?php

namespace App\Services\Hr;

use App\Models\User;
use App\Notifications\EmployeeCredentialsNotification;
use App\Notifications\HrNoticeNotification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * HR emails. Like AccountMailService, a failed send is logged and swallowed:
 * an approved leave stays approved even if the mail server is down.
 *
 * `credentials()` reports whether the send worked so HR can be told to share
 * the password another way.
 */
class HrMailService
{
    public function credentials(User $user, string $employeeCode, string $password, bool $isReset = false): bool
    {
        return $this->safely($user, new EmployeeCredentialsNotification($employeeCode, $password, $isReset));
    }

    /**
     * @param  list<string>  $lines
     */
    public function notice(?User $user, string $subject, array $lines, ?string $actionLabel = null, ?string $actionUrl = null): bool
    {
        if (! $user) {
            return false;
        }

        return $this->safely($user, new HrNoticeNotification($subject, $subject, $lines, $actionLabel, $actionUrl));
    }

    private function safely(User $user, object $notification): bool
    {
        if (empty($user->email)) {
            return false;
        }

        try {
            $user->notify($notification);

            return true;
        } catch (Throwable $e) {
            Log::warning('HR email could not be sent', [
                'user_id' => $user->id,
                'notification' => $notification::class,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
