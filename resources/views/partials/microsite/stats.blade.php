@php $stats = $microsite->layout()->stats(true); @endphp
@if (! empty($stats))
    @php $heading = $microsite->layout()->heading('stats'); @endphp
    <section id="highlights" class="msite-section bg-brand text-white">
        <div class="msite-container">
            <div class="text-center mw-2xl mx-auto mb-12 reveal" data-reveal="zoom">
                @if ($heading['eyebrow'] !== '')
                    <p class="fs-sm fw-semibold text-gold text-uppercase tracking-wide mb-2">{{ $heading['eyebrow'] }}</p>
                @endif
                <h2 class="msite-heading fs-3xl fs-4xl text-white">{{ $heading['title'] }}</h2>
                @if ($heading['subtitle'] !== '')
                    <p class="text-white text-opacity-75 mt-3">{{ $heading['subtitle'] }}</p>
                @endif
            </div>
            <div class="d-grid grid-cols-2 grid-cols-md-{{ min(count($stats), 4) }} gap-6 text-center">
                @foreach ($stats as $stat)
                    <div class="reveal p-4 rounded-4 bg-white bg-opacity-10" style="transition-delay: {{ ($loop->index % 4) * 0.08 }}s">
                        {{-- count-up.js animates the number and keeps the suffix. --}}
                        <p class="fs-4xl fw-bolder text-gold" data-countup="{{ $stat['value'].$stat['suffix'] }}">{{ $stat['value'].$stat['suffix'] }}</p>
                        <p class="fs-sm text-uppercase tracking-wide text-white text-opacity-75 mt-1">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
