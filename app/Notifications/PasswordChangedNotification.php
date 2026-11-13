<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Security notice sent whenever an account's password changes — by the user,
 * through the forgot-password flow, or by someone above them in the hierarchy.
 */
class PasswordChangedNotification extends Notification
{
    public function __construct(public ?string $changedBy = null) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your '.config('app.name').' password was changed')
            ->view('emails.password-changed', [
                'name' => $notifiable->name,
                'changedBy' => $this->changedBy,
                'changedAt' => now(),
                'resetUrl' => route('password.request'),
            ]);
    }
}
