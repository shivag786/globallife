<x-layouts.app :title="'Commission — '.$category->name" heading="Category Commission">
    <div class="mb-4">
        <a href="{{ route('admin.commissions.index') }}" class="text-sm text-indigo-600 hover:underline">← Back to commissions</a>
    </div>
    <p class="text-sm text-slate-500 mb-6 max-w-xl">
        Commission for all products in <strong class="text-slate-700">{{ $category->name }}</strong>.
        Any role left blank falls back to the global default. A product can still override this.
    </p>

    <form method="POST" action="{{ route('admin.commissions.category.update', $category) }}" class="max-w-xl bg-white rounded-lg shadow-sm border border-slate-100 p-6">
        @csrf
        @method('PUT')
        @include('admin.commissions._rules_fields')
        <button type="submit" class="mt-6 bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">Save Category Commission</button>
    </form>
</x-layouts.app>
