<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * The forgot-password email, in the site's own branded template instead of the
 * framework default. The link and token handling are inherited unchanged.
 */
class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Reset your '.config('app.name').' password')
            ->view('emails.reset-password', [
                'name' => $notifiable->name,
                'url' => $this->resetUrl($notifiable),
                'minutes' => $minutes,
            ]);
    }
}
