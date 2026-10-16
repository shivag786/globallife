<x-layouts.public title="Welcome">
    @forelse ($sections as $section)
        @include("partials.home-sections.{$section->type}", ['section' => $section])
    @empty
        <div class="text-center mw-2xl mx-auto py-16">
            <h1 class="fs-4xl fw-bold mb-4">{{ config('app.name') }}</h1>
            <p class="text-secondary mb-8">
                An MLM business ecosystem with city managers, VIP memberships, microsites, and analytics.
            </p>
        </div>
    @endforelse
</x-layouts.public>
