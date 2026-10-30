<x-layouts.app :title="'Order '.$order->order_number" :heading="'Order '.$order->order_number">
    @php $money = fn ($n) => '₹'.number_format((float) $n, 2); @endphp

    <a href="{{ route('vip.orders.index') }}" class="fs-sm text-brand hover-underline d-inline-block mb-4">← All orders</a>

    <div class="d-grid gap-6 grid-template-lg align-items-start" style="--cols-lg: minmax(0, 1fr) 360px">
        <div class="d-flex flex-column gap-6">
            <div class="bg-body border rounded-3 p-5">
                <x-order-tracker :order="$order" />
            </div>

            <div class="bg-body border rounded-3 overflow-hidden">
                <div class="px-5 py-3 border-bottom fw-semibold">Items to pack</div>
                <div class="divide-y">
                    @foreach ($order->items as $item)
                        <div class="d-flex justify-content-between gap-4 px-5 py-3 fs-sm">
                            <div>
                                <span class="fw-medium">{{ $item->product_name }}</span>
                                @if ($item->product_sku)
                                    <span class="d-block fs-xs text-secondary">SKU {{ $item->product_sku }}</span>
                                @endif
                            </div>
                            <div class="text-end text-nowrap">
                                {{ $item->quantity }} × {{ $money($item->unit_price) }}
                                <span class="d-block fw-semibold">{{ $money($item->line_total) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="px-5 py-3 border-top d-flex justify-content-between fw-semibold">
                    <span>Total</span><span>{{ $money($order->total) }}</span>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column gap-4">
            <div class="bg-body border rounded-3 p-5">
                <p class="fs-xs text-uppercase text-secondary mb-2">Deliver to</p>
                <p class="fw-semibold mb-1">{{ $order->customer_name }}</p>
                <p class="fs-sm mb-1">
                    <a href="tel:{{ $order->customer_phone }}" class="text-brand hover-underline">{{ $order->customer_phone }}</a>
                </p>
                <p class="fs-sm text-secondary mb-0 text-prewrap">{{ $order->address }}
{{ $order->city }}, {{ $order->state }} {{ $order->pincode }}</p>
                @if ($order->delivery_notes)
                    <p class="fs-sm mt-3 mb-0 p-3 rounded-2 bg-warning-subtle">📝 {{ $order->delivery_notes }}</p>
                @endif
            </div>

            <div class="bg-body border rounded-3 p-5">
                <p class="fs-xs text-uppercase text-secondary mb-2">Payment</p>
                @if ($order->payment_method === 'cod')
                    <p class="fw-semibold text-warning-emphasis mb-1">Cash on Delivery</p>
                    <p class="fs-sm text-secondary mb-0">Collect <strong>{{ $money($order->total) }}</strong> from the customer at delivery.</p>
                @else
                    <p class="fw-semibold text-success mb-1">Paid online</p>
                    <p class="fs-sm text-secondary mb-0">
                        @if ($order->settlement)
                            Transferred to you on <x-ist :value="$order->settlement->paid_at" format="d M Y" :suffix="''" />
                            @if ($order->settlement->utr_number) (UTR {{ $order->settlement->utr_number }}) @endif.
                        @elseif ($order->isDelivered())
                            The company will transfer {{ $money($order->total) }} to you.
                        @else
                            The company transfers this to you once the order is delivered.
                        @endif
                    </p>
                @endif
            </div>

            <div class="bg-body border rounded-3 p-5">
                <p class="fs-xs text-uppercase text-secondary mb-3">Update status</p>
                @forelse ($nextStatuses as $status => $label)
                    <form method="POST" action="{{ route('vip.orders.update-status', $order) }}" class="mb-2"
                          @if ($status === 'delivered') data-confirm="Mark this order as delivered? Do this only after the customer has received it." data-confirm-button="Yes, delivered" @endif>
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ $status }}">
                        <button type="submit" class="btn w-100 {{ $loop->first ? 'btn-brand' : 'btn-outline-secondary' }}">
                            Mark as “{{ $label }}”
                        </button>
                    </form>
                @empty
                    <p class="fs-sm text-secondary mb-0">
                        {{ $order->isCancelled() ? 'This order was cancelled.' : 'Delivered — nothing more to do.' }}
                    </p>
                @endforelse
                @if ($nextStatuses)
                    <p class="fs-xs text-secondary mt-3 mb-0">
                        Need to cancel? Contact the company — online payments are refunded from its account.
                    </p>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
