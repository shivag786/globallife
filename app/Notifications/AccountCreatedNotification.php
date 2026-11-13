<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Welcome email for an account someone else created (Sub Admin, Branch Manager,
 * Commission Partner, VIP Member). The password is never emailed: the creator
 * shares it, or the new user sets their own through "Forgot password".
 */
class AccountCreatedNotification extends Notification
{
    public function __construct(public ?string $createdBy = null) {}

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
            ->subject('Your '.config('app.name').' account is ready')
            ->view('emails.account-created', [
                'name' => $notifiable->name,
                'email' => $notifiable->email,
                'role' => $notifiable->getRoleNames()->map(fn ($r) => ucwords(str_replace('_', ' ', $r)))->implode(', '),
                'createdBy' => $this->createdBy,
                'loginUrl' => route('login'),
                'resetUrl' => route('password.request'),
            ]);
    }
}
