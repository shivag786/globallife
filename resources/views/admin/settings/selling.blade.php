<x-layouts.app title="Selling Options" heading="Selling Options by Package">
    <p class="fs-sm text-secondary mb-5 mw-3xl">
        Decide, per package, how businesses sell <strong>their own products</strong> on their page.
        This does not affect the company products they resell under My Store.
    </p>

    <div class="d-grid grid-cols-md-2 gap-4 mb-6 mw-5xl">
        <div class="bg-body border rounded-3 p-5">
            <p class="fw-semibold mb-1">📒 Catalogue only</p>
            <p class="fs-sm text-secondary mb-0">
                Products are shown with price and details. Each one has an <strong>Order on WhatsApp</strong>
                button that opens a chat with the business, with the order already typed. No cart, no payment
                through us.
            </p>
        </div>
        <div class="bg-body border rounded-3 p-5">
            <p class="fw-semibold mb-1">🛒 Direct sell</p>
            <p class="fs-sm text-secondary mb-0">
                Customers add to cart, choose a delivery address and pay by <strong>Razorpay or Cash on Delivery</strong>.
                Online money comes to the company's Razorpay; the business delivers and you transfer it to them
                from <a href="{{ route('admin.business-sales.index') }}" class="text-brand hover-underline">Business Sales</a>.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.selling.update') }}" class="mw-5xl">
        @csrf
        @method('PUT')

        <div class="bg-body rounded-3 shadow-sm border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-100 fs-sm">
                    <thead class="bg-body-tertiary text-secondary">
                        <tr>
                            <th class="px-4 py-3">Package</th>
                            <th class="px-4 py-3 text-end">Businesses</th>
                            <th class="px-4 py-3">How they sell their own products</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($plans as $plan)
                            <tr class="border-top">
                                <td class="px-4 py-4">
                                    <span class="fw-semibold">{{ $plan->name }}</span>
                                    <span class="d-block fs-xs text-secondary">
                                        ₹{{ number_format((float) $plan->joining_price) }} · {{ $plan->validityLabel() }} · up to {{ $plan->productLimit() }} products
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-end font-monospace">{{ $plan->microsites_count }}</td>
                                <td class="px-4 py-4">
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach (['catalogue' => 'Catalogue only (WhatsApp)', 'direct' => 'Direct sell (cart & payment)'] as $mode => $label)
                                            <label class="d-inline-flex align-items-center gap-2 border rounded-pill px-3 py-2 mb-0">
                                                <input type="radio" class="form-check-input m-0" name="modes[{{ $plan->id }}]" value="{{ $mode }}"
                                                       @checked(old('modes.'.$plan->id, $plan->selling_mode) === $mode)>
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-brand">Save selling options</button>
        </div>
    </form>
</x-layouts.app>
