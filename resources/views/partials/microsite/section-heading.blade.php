{{-- The eyebrow / title / subtitle above a microsite section, as the owner set
     them on the page builder (BusinessPageLayout falls back to the defaults). --}}
<div class="text-center mw-2xl mx-auto mb-14 reveal" data-reveal="zoom">
    @if ($heading['eyebrow'] !== '')
        <p class="fs-sm fw-semibold text-gold text-uppercase tracking-wide mb-2">{{ $heading['eyebrow'] }}</p>
    @endif
    <h2 class="msite-heading fs-3xl fs-4xl">{{ $heading['title'] }}</h2>
    @if ($heading['subtitle'] !== '')
        <p class="text-secondary mt-3">{{ $heading['subtitle'] }}</p>
    @endif
</div>
