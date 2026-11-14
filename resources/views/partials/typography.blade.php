{{-- Admin-chosen fonts (Site Settings → Typography). Must come AFTER @vite so
     these :root variables override the compiled defaults. --}}
@php $typography = app(\App\Services\TypographyService::class); @endphp
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="{{ $typography->googleFontsUrl() }}" rel="stylesheet">
<style>{!! $typography->cssVariables() !!}</style>
