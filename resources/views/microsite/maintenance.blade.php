@php
    $settings = app(\App\Services\SettingsService::class)->all();
    $siteName = $settings['site_title'] ?? config('app.name');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Under Maintenance · {{ $siteName }}</title>
    {{-- A lapsed plan is a temporary state, so keep this page out of the index. --}}
    <meta name="robots" content="noindex, nofollow">
    @if (! empty($settings['favicon']))
        <link rel="icon" href="{{ asset('storage/'.$settings['favicon']) }}">
    @endif
    @vite(['resources/css/app.scss'])
</head>
<body class="bg-body-tertiary min-vh-100 d-flex align-items-center justify-content-center px-4 py-12">
    <main class="w-100 mw-lg bg-body rounded-4 shadow-sm border p-8 p-sm-10 text-center">
        <div class="w-16 h-16 mx-auto rounded-pill bg-warning-subtle d-flex align-items-center justify-content-center mb-6" aria-hidden="true">
            <svg class="w-8 h-8 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z"/>
            </svg>
        </div>

        <h1 class="fs-2xl fw-bold text-body">We&rsquo;re Under Maintenance</h1>

        <p class="mt-3 text-secondary lh-lg">
            @if ($microsite->business_name)
                <span class="fw-semibold text-body-secondary">{{ $microsite->business_name }}</span>&rsquo;s page is
            @else
                This page is
            @endif
            temporarily unavailable while we carry out some scheduled maintenance.
            Please check back again shortly &mdash; thank you for your patience.
        </p>

        <div class="mt-8 pt-6 border-top">
            <a href="{{ url('/') }}" class="btn btn-brand btn-sm d-inline-block">
                Go to {{ $siteName }}
            </a>
        </div>
    </main>
</body>
</html>
