<x-layouts.app :title="'Commission — '.$category->name" heading="Category Commission">
    <div class="mb-4">
        <a href="{{ route('admin.commissions.index') }}" class="fs-sm text-primary hover-underline">← Back to commissions</a>
    </div>
    <p class="fs-sm text-secondary mb-6 mw-xl">
        Commission for all products in <strong class="text-body-secondary">{{ $category->name }}</strong>.
        Any role left blank falls back to the global default. A product can still override this.
    </p>

    <form method="POST" action="{{ route('admin.commissions.category.update', $category) }}" class="mw-xl bg-body rounded-2 shadow-sm border p-6">
        @csrf
        @method('PUT')
        @include('admin.commissions._rules_fields')
        <button type="submit" class="btn btn-primary btn-sm mt-6">Save Category Commission</button>
    </form>
</x-layouts.app>
