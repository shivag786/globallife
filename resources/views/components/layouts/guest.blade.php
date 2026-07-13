<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <a href="{{ url('/') }}" class="text-2xl font-bold text-slate-800">{{ config('app.name') }}</a>
        </div>
        <div class="bg-white shadow-md rounded-lg p-8">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
