<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Check the SMTP (Hostinger) settings in .env actually deliver:
//   php artisan mail:test you@example.com
Artisan::command('mail:test {to}', function (string $to) {
    $this->info('Mailer: '.config('mail.default').' | host: '.config('mail.mailers.smtp.host').':'.config('mail.mailers.smtp.port').' | from: '.config('mail.from.address'));

    try {
        Mail::raw('This is a test email from '.config('app.name').'. If you can read this, email sending works.', function ($m) use ($to) {
            $m->to($to)->subject(config('app.name').' test email');
        });
    } catch (Throwable $e) {
        $this->error('Sending failed: '.$e->getMessage());

        return 1;
    }

    $this->info(config('mail.default') === 'log'
        ? 'MAIL_MAILER is "log": the email was written to storage/logs/laravel.log, not sent. Set MAIL_MAILER=smtp to send for real.'
        : "Sent to {$to}. Check the inbox (and spam folder).");

    return 0;
})->purpose('Send a test email using the configured mailer');
