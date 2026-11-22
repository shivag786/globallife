<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * A short HR update email: leave approved / rejected, payslip ready, expense
 * claim decided, a leave request waiting for a manager, and so on.
 */
class HrNoticeNotification extends Notification
{
    /**
     * @param  list<string>  $lines
     */
    public function __construct(
        public string $subject,
        public string $heading,
        public array $lines,
        public ?string $actionLabel = null,
        public ?string $actionUrl = null,
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
            ->subject($this->subject)
            ->view('emails.hr-notice', [
                'name' => $notifiable->name,
                'heading' => $this->heading,
                'lines' => $this->lines,
                'actionLabel' => $this->actionLabel,
                'actionUrl' => $this->actionUrl,
            ]);
    }
}
