<x-layouts.public title="Welcome">
    @forelse ($sections as $section)
        @include("partials.home-sections.{$section->type}", ['section' => $section])
    @empty
        <div class="text-center max-w-2xl mx-auto py-16">
            <h1 class="text-4xl font-bold mb-4">{{ config('app.name') }}</h1>
            <p class="text-slate-500 mb-8">
                An MLM business ecosystem with city managers, VIP memberships, microsites, and analytics.
            </p>
            <a href="{{ route('vip-plans.index') }}" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-md font-medium hover:bg-indigo-700">
                Explore VIP Plans
            </a>
        </div>
    @endforelse
</x-layouts.public>
