@extends('emails.layout')

@section('title', 'Reset your password')
@section('tagline', 'Password reset')

@section('body')
    <p style="margin:0 0 12px;font-size:16px;color:#1f2d27;">Hi {{ $name }},</p>
    <p style="margin:0;">
        We received a request to reset the password for your account. Click the button below to choose a new one.
    </p>

    @include('emails.partials.button', ['url' => $url, 'label' => 'Reset password'])

    <p style="margin:20px 0 0;">
        This link expires in {{ $minutes }} minutes. If you did not ask for a password reset, you can ignore
        this email — your password will stay the same.
    </p>
@endsection
