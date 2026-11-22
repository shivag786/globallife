@extends('emails.layout')

@section('title', 'Your account is ready')
@section('tagline', 'Welcome aboard')

@section('body')
    <p style="margin:0 0 12px;font-size:16px;color:#1f2d27;">Hi {{ $name }},</p>
    <p style="margin:0;">
        Your {{ config('app.name') }} account has been created
        @if ($createdBy) by <strong>{{ $createdBy }}</strong> @endif.
        Here are your login details:
    </p>

    <table role="presentation" width="100%" style="margin:16px 0;background:#f0f7f3;border:1px solid #cfe6da;border-radius:8px;">
        <tr><td style="padding:16px 18px;font-size:14px;line-height:1.8;">
            Login email: <strong style="color:#1f2d27;">{{ $email }}</strong><br>
            @if ($password)
                Password: <strong style="color:#1f2d27;font-family:monospace;font-size:15px;">{{ $password }}</strong><br>
            @else
                Password: the one shared with you{{ $createdBy ? ' by '.$createdBy : '' }}.<br>
            @endif
            @if ($role) Role: <strong style="color:#1f2d27;">{{ $role }}</strong> @endif
        </td></tr>
    </table>

    @include('emails.partials.button', ['url' => $loginUrl, 'label' => 'Log in'])

    @if ($password)
        <p style="margin:20px 0 0;color:#8a6d1f;">
            For your security, please change this password after logging in (<em>Change Password</em>),
            and do not share it with anyone.
        </p>
    @endif
    <p style="margin:12px 0 0;">
        Forgot it later? Use <a href="{{ $resetUrl }}" style="color:#1b3c2c;">Forgot password</a> on the login page.
    </p>
@endsection
