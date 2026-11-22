@extends('emails.layout')

@section('title', $heading)
@section('tagline', 'HR update')

@section('body')
    <p style="margin:0 0 12px;font-size:16px;color:#1f2d27;">Hi {{ $name }},</p>

    @foreach ($lines as $line)
        <p style="margin:0 0 10px;">{{ $line }}</p>
    @endforeach

    @if ($actionUrl)
        @include('emails.partials.button', ['url' => $actionUrl, 'label' => $actionLabel ?? 'Open'])
    @endif
@endsection
