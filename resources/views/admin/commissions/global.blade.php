<x-layouts.app title="Global Commission Defaults" heading="Global Commission Defaults">
    <div class="mb-4">
        <a href="{{ route('admin.commissions.index') }}" class="text-sm text-indigo-600 hover:underline">← Back to commissions</a>
    </div>
    <p class="text-sm text-slate-500 mb-6 max-w-xl">
        These percentages (or fixed amounts) apply to <strong>every</strong> product unless a category
        or product sets its own. Commission is calculated on the price the customer pays.
    </p>

    <form method="POST" action="{{ route('admin.commissions.global.update') }}" class="max-w-xl bg-white rounded-lg shadow-sm border border-slate-100 p-6">
        @csrf
        @method('PUT')
        @include('admin.commissions._rules_fields')
        <button type="submit" class="mt-6 bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">Save Defaults</button>
    </form>
</x-layouts.app>
