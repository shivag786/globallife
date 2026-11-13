<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\AccountPasswordService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        // Reached from the emailed link while logged out, so every existing
        // session for the account is dropped (the old password may be the very
        // reason for the reset) and a security notice is emailed.
        app(AccountPasswordService::class)->setOwnPassword($user, $input['password']);
    }
}
