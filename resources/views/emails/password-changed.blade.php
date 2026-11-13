@extends('emails.layout')

@section('title', 'Your password was changed')
@section('tagline', 'Security notice')

@section('body')
    <p style="margin:0 0 12px;font-size:16px;color:#1f2d27;">Hi {{ $name }},</p>
    <p style="margin:0;">
        The password for your account was changed
        @if ($changedBy) by <strong>{{ $changedBy }}</strong> @endif
        on {{ $changedAt->copy()->timezone('Asia/Kolkata')->format('d M Y, h:i A') }} IST.
        You have been signed out of other devices.
    </p>

    @if ($changedBy)
        <p style="margin:12px 0 0;">Please contact them for your new password, or set your own using the link below.</p>
    @else
        <p style="margin:12px 0 0;">If this was you, no action is needed.</p>
    @endif

    <p style="margin:12px 0 0;">
        <strong style="color:#1f2d27;">Didn't expect this?</strong>
        Reset your password right away and let us know.
    </p>

    @include('emails.partials.button', ['url' => $resetUrl, 'label' => 'Reset my password'])
@endsection
