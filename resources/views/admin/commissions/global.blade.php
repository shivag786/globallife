<x-layouts.app title="Global Commission Defaults" heading="Global Commission Defaults">
    <div class="mb-4">
        <a href="{{ route('admin.commissions.index') }}" class="fs-sm text-primary hover-underline">← Back to commissions</a>
    </div>
    <p class="fs-sm text-secondary mb-6 mw-xl">
        These percentages (or fixed amounts) apply to <strong>every</strong> product unless a category
        or product sets its own. Commission is calculated on the price the customer pays.
    </p>

    <form method="POST" action="{{ route('admin.commissions.global.update') }}" class="mw-xl bg-body rounded-2 shadow-sm border p-6">
        @csrf
        @method('PUT')
        @include('admin.commissions._rules_fields')
        <button type="submit" class="btn btn-primary btn-sm mt-6">Save Defaults</button>
    </form>
</x-layouts.app>
