@extends('emails.layout')

@section('title', $isReset ? 'Your password was reset' : 'Your employee account is ready')
@section('tagline', $isReset ? 'New login details' : 'Welcome to the team')

@section('body')
    <p style="margin:0 0 12px;font-size:16px;color:#1f2d27;">Hi {{ $name }},</p>
    <p style="margin:0;">
        @if ($isReset)
            HR has reset the password of your {{ config('app.name') }} employee account. Your new login details are below.
        @else
            Your {{ config('app.name') }} employee account has been created. Use the details below to log in to the
            employee portal, where you can mark attendance, apply for leave and download your payslips.
        @endif
    </p>

    <table role="presentation" width="100%" style="margin:16px 0;background:#f0f7f3;border:1px solid #cfe6da;border-radius:8px;">
        <tr><td style="padding:16px 18px;font-size:14px;line-height:1.8;">
            Employee ID (User ID): <strong style="color:#1f2d27;">{{ $employeeCode }}</strong><br>
            Login email: <strong style="color:#1f2d27;">{{ $email }}</strong><br>
            Password: <strong style="color:#1f2d27;font-family:monospace;font-size:15px;">{{ $password }}</strong>
        </td></tr>
    </table>

    <p style="margin:0 0 4px;">You can log in with either your <strong>Employee ID</strong> or your <strong>email</strong>.</p>

    @include('emails.partials.button', ['url' => $loginUrl, 'label' => 'Log in to the employee portal'])

    <p style="margin:20px 0 0;color:#8a6d1f;">
        For your security, please change this password after logging in (<em>Change Password</em> at the top of the portal),
        and do not share it with anyone.
    </p>
@endsection
