<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Login details for a new employee (or after HR resets their password).
 *
 * Unlike AccountCreatedNotification this one DOES carry the password: HR
 * asked for credentials to reach the employee directly. The employee is told
 * to change it after the first login.
 */
class EmployeeCredentialsNotification extends Notification
{
    public function __construct(
        public string $employeeCode,
        public string $password,
        public bool $isReset = false,
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
            ->subject($this->isReset
                ? 'Your '.config('app.name').' employee password was reset'
                : 'Welcome to '.config('app.name').' - your employee login')
            ->view('emails.employee-credentials', [
                'name' => $notifiable->name,
                'email' => $notifiable->email,
                'employeeCode' => $this->employeeCode,
                'password' => $this->password,
                'isReset' => $this->isReset,
                'loginUrl' => route('login'),
            ]);
    }
}
