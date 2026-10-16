<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-body-tertiary min-vh-100 d-flex align-items-center justify-content-center px-3 py-4">
    <div class="w-100" style="max-width: 28rem">
        <div class="text-center mb-4">
            <a href="{{ url('/') }}" class="fs-3 fw-bold text-body text-decoration-none">{{ config('app.name') }}</a>
        </div>
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-sm-5">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
