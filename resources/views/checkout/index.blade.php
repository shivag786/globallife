<x-layouts.public title="Checkout">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
        $field = 'mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500';
    @endphp

    <div class="max-w-5xl mx-auto px-6 py-16">
        <h1 class="font-display text-3xl font-bold text-brand-900 mb-8">Checkout</h1>

        @if ($errors->any())
            <div class="mb-6 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg p-4">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('checkout.store') }}" class="grid lg:grid-cols-[1fr_340px] gap-8 items-start">
            @csrf
            <div class="space-y-6">
                {{-- Contact + shipping --}}
                <div class="bg-white border border-slate-100 rounded-2xl p-6">
                    <h2 class="font-semibold text-brand-900 mb-4">Delivery Details</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Full Name</label>
                            <input type="text" name="customer_name" value="{{ old('customer_name', $prefill?->name) }}" required class="{{ $field }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Email</label>
                            <input type="email" name="customer_email" value="{{ old('customer_email', $prefill?->email) }}" required class="{{ $field }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Mobile</label>
                            <input type="text" name="customer_phone" value="{{ old('customer_phone', $prefill?->mobile) }}" required class="{{ $field }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">PIN Code</label>
                            <input type="text" name="pincode" value="{{ old('pincode') }}" required class="{{ $field }}">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Address</label>
                            <textarea name="address" rows="2" required class="{{ $field }}">{{ old('address') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">City</label>
                            <input type="text" name="city" value="{{ old('city') }}" required class="{{ $field }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">State</label>
                            <input type="text" name="state" value="{{ old('state') }}" required class="{{ $field }}">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Delivery Notes <span class="text-slate-400">(optional)</span></label>
                            <textarea name="delivery_notes" rows="2" class="{{ $field }}">{{ old('delivery_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Payment --}}
                <div class="bg-white border border-slate-100 rounded-2xl p-6">
                    <h2 class="font-semibold text-brand-900 mb-1">Payment Method</h2>
                    <p class="text-xs text-slate-400 mb-4">Using a test gateway — no real charge is made.</p>
                    <div class="space-y-2.5">
                        @foreach ([
                            'online_success' => ['Test Payment — Success', 'Simulates a successful online payment.'],
                            'cod' => ['Cash on Delivery', 'Pay in cash when your order arrives.'],
                            'online_fail' => ['Test Payment — Failure', 'Simulates a declined payment (to test the flow).'],
                        ] as $value => $opt)
                            <label class="flex items-start gap-3 border border-slate-200 rounded-lg p-3.5 cursor-pointer hover:border-brand-400 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/50 transition">
                                <input type="radio" name="payment_choice" value="{{ $value }}" @checked(old('payment_choice', 'online_success') === $value) class="mt-0.5 text-brand-600 focus:ring-brand-500">
                                <span>
                                    <span class="block text-sm font-medium text-slate-800">{{ $opt[0] }}</span>
                                    <span class="block text-xs text-slate-500">{{ $opt[1] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Summary --}}
            <div class="bg-white border border-slate-100 rounded-2xl p-6 premium-shadow lg:sticky lg:top-24">
                <h2 class="font-semibold text-brand-900 mb-4">Your Order</h2>
                <div class="space-y-3 max-h-64 overflow-y-auto mb-4">
                    @foreach ($items as $item)
                        <div class="flex items-center gap-3 text-sm">
                            <span class="w-6 h-6 rounded-full bg-brand-700 text-white text-xs flex items-center justify-center flex-shrink-0">{{ $item['quantity'] }}</span>
                            <span class="flex-1 min-w-0 truncate text-slate-700">{{ $item['product']->name }}</span>
                            <span class="font-medium">{{ $money($item['line_total']) }}</span>
                        </div>
                    @endforeach
                </div>
                <dl class="space-y-2 text-sm border-t border-slate-100 pt-4">
                    <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd>{{ $money($totals['subtotal']) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Shipping</dt><dd>{{ $totals['shipping'] > 0 ? $money($totals['shipping']) : 'Free' }}</dd></div>
                    <div class="flex justify-between text-base font-bold text-brand-900 border-t border-slate-100 pt-3"><dt>Total</dt><dd>{{ $money($totals['total']) }}</dd></div>
                </dl>
                <button type="submit" class="mt-6 w-full bg-brand-700 text-white py-3 rounded-full font-medium hover:bg-brand-800 transition">Place Order</button>
                <a href="{{ route('cart.index') }}" class="mt-3 block text-center text-sm text-slate-500 hover:text-brand-700">← Back to cart</a>
            </div>
        </form>
    </div>
</x-layouts.public>
