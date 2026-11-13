@extends('emails.layout')

@section('title', 'Your account is ready')
@section('tagline', 'Welcome aboard')

@section('body')
    <p style="margin:0 0 12px;font-size:16px;color:#1f2d27;">Hi {{ $name }},</p>
    <p style="margin:0;">
        Your {{ config('app.name') }} account has been created
        @if ($createdBy) by <strong>{{ $createdBy }}</strong> @endif.
    </p>

    <table role="presentation" width="100%" style="margin:16px 0;background:#f0f7f3;border:1px solid #cfe6da;border-radius:8px;">
        <tr><td style="padding:16px 18px;font-size:13px;">
            Login email: <strong style="color:#1f2d27;">{{ $email }}</strong><br>
            @if ($role) Role: <strong style="color:#1f2d27;">{{ $role }}</strong><br> @endif
            Password: the one shared with you{{ $createdBy ? ' by '.$createdBy : '' }}.
        </td></tr>
    </table>

    @include('emails.partials.button', ['url' => $loginUrl, 'label' => 'Log in'])

    <p style="margin:20px 0 0;">
        Don't have your password, or want to set your own? Use
        <a href="{{ $resetUrl }}" style="color:#1b3c2c;">Forgot password</a> on the login page.
        You can also change it any time from <em>Change Password</em> after logging in.
    </p>
@endsection
