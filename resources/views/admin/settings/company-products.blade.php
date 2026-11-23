<x-layouts.app title="Hide Company Products" heading="Hide Company Products">
    <p class="fs-sm text-secondary mb-5 mw-3xl">
        One switch to take the company's own products off sale everywhere. Nothing is deleted:
        products and each business's My Store choices are kept,
        and come back exactly as they were when you show the products again.
    </p>

    <div @class(['border rounded-3 p-5 mb-6 mw-3xl',
                 'bg-warning-subtle border-warning' => $hidden, 'bg-body' => ! $hidden])>
        <p class="fw-semibold fs-lg mb-2">
            Company products:
            @if ($hidden)
                <span class="badge text-bg-warning">Hidden</span>
            @else
                <span class="badge text-bg-success">On sale</span>
            @endif
        </p>
        <p class="fs-sm text-secondary mb-4">
            {{ number_format($activeProducts) }} active {{ \Illuminate\Support\Str::plural('product', $activeProducts) }} in
            <a href="{{ route('admin.products.index') }}" class="text-brand hover-underline">Products</a>.
        </p>

        <form method="POST" action="{{ route('admin.settings.company-products.toggle') }}"
              onsubmit="return confirm('{{ $hidden ? 'Show company products again?' : 'Hide company products everywhere?' }}')">
            @csrf
            <button type="submit" @class(['btn', 'btn-brand' => $hidden, 'btn-danger' => ! $hidden])>
                {{ $hidden ? 'Show company products' : 'Hide company products' }}
            </button>
        </form>
    </div>

    <div class="bg-body border rounded-3 p-5 mw-3xl">
        <p class="fw-semibold mb-2">While hidden</p>
        <ul class="fs-sm text-secondary mb-0 ps-4">
            <li>The shop, homepage product sections and product pages show no company products, and the
                "Products" link leaves the site menu.</li>
            <li>Company products cannot be added to a cart; any already in a cart or wishlist drop out.</li>
            <li>Business pages stop showing company products, and VIP members cannot change My Store.</li>
            <li>The <strong>Catalogue only</strong> and <strong>Direct sell</strong> boxes at the top of
                <a href="{{ route('admin.settings.selling.edit') }}" class="text-brand hover-underline">Selling Options by Package</a>
                are hidden. Each package keeps its selling option, and it can still be changed there.</li>
            <li>Admin &rarr; Products still lists and edits everything.</li>
        </ul>
    </div>
</x-layouts.app>
