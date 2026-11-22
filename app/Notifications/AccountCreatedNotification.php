<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Welcome email for a new account (Sub Admin, Branch Manager, Commission
 * Partner, VIP Member, or a customer registering at checkout).
 *
 * It carries the login email AND the password, by the owner's decision, so a
 * new user can log in straight from the email. The email tells them to change
 * it after the first login. (Employees get EmployeeCredentialsNotification,
 * which also includes their Employee ID.)
 */
class AccountCreatedNotification extends Notification
{
    public function __construct(
        public ?string $createdBy = null,
        public ?string $password = null,
    ) {}

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
            ->subject('Your '.config('app.name').' account is ready - login details inside')
            ->view('emails.account-created', [
                'name' => $notifiable->name,
                'email' => $notifiable->email,
                'password' => $this->password,
                'role' => $notifiable->getRoleNames()->map(fn ($r) => ucwords(str_replace('_', ' ', $r)))->implode(', '),
                'createdBy' => $this->createdBy,
                'loginUrl' => route('login'),
                'resetUrl' => route('password.request'),
            ]);
    }
}
