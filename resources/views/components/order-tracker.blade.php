@props(['order'])

@php
    $expected = app(\App\Services\DeliveryService::class)->expectedFor($order);
    $rank = \App\Models\Order::STATUS_RANK[$order->status] ?? 0;

    $steps = [
        ['key' => 'confirmed',  'label' => 'Order Confirmed', 'at' => $order->placed_at,    'desc' => 'We’ve received your order'],
        ['key' => 'processing', 'label' => 'Packed',          'at' => $order->processing_at, 'desc' => 'Your item is packed and ready'],
        ['key' => 'dispatched', 'label' => 'Shipped',         'at' => $order->dispatched_at, 'desc' => 'On its way to your address'],
        ['key' => 'delivered',  'label' => 'Delivered',       'at' => $order->delivered_at,  'desc' => 'Order delivered'],
    ];
@endphp

<div class="bg-body border rounded-4 overflow-hidden">
    {{-- Headline: arriving-by / delivered / cancelled --}}
    @if ($order->isCancelled())
        <div class="d-flex align-items-center gap-3 px-5 px-sm-6 py-4 bg-danger-subtle border-bottom border-danger-subtle">
            <x-icon name="x-mark" class="w-6 h-6 text-danger flex-shrink-0" />
            <div>
                <p class="fw-semibold text-danger text-capitalize">Order {{ $order->status }}</p>
                <p class="fs-sm">This order was {{ $order->status }} and will not be delivered.</p>
            </div>
        </div>
    @elseif ($order->isDelivered())
        <div class="d-flex align-items-center gap-3 px-5 px-sm-6 py-4 bg-success-subtle border-bottom border-success-subtle">
            <x-icon name="check-circle" class="w-6 h-6 text-success flex-shrink-0" />
            <div>
                <p class="fw-semibold text-success">Delivered</p>
                <p class="fs-sm">Delivered on <x-ist :value="$order->delivered_at" format="d M Y" :suffix="''" /></p>
            </div>
        </div>
    @else
        <div class="d-flex align-items-center gap-3 px-5 px-sm-6 py-4 bg-brand-subtle border-bottom border-brand-subtle">
            <x-icon name="truck" class="w-6 h-6 text-brand flex-shrink-0" />
            <div>
                <p class="fw-semibold text-brand-emphasis">Arriving by
                    <span class="text-brand">{{ optional($expected)->timezone('Asia/Kolkata')->format('D, d M Y') ?? 'soon' }}</span>
                </p>
                <p class="fs-sm text-secondary">We’ll keep this updated as your order moves.</p>
            </div>
        </div>
    @endif

    {{-- Vertical stepper --}}
    @unless ($order->isCancelled())
        <ol class="px-5 px-sm-6 py-5">
            @foreach ($steps as $i => $step)
                @php
                    $stepRank = \App\Models\Order::STATUS_RANK[$step['key']] ?? 0;
                    $done = $rank >= $stepRank;
                    $isCurrent = $order->status === $step['key'] && ! $order->isDelivered();
                    $isLast = $i === count($steps) - 1;
                @endphp
                <li class="d-flex gap-4 {{ $isLast ? '' : 'pb-6' }} position-relative">
                    {{-- Connector line --}}
                    @unless ($isLast)
                        <span class="position-absolute top-6 bottom-0 w-0.5 {{ $rank > $stepRank ? 'bg-success' : 'bg-body-secondary' }}"></span>
                    @endunless

                    {{-- Dot --}}
                    <span class="position-relative z-1 flex-shrink-0 w-6 h-6 rounded-pill d-flex align-items-center justify-content-center {{ $done ? ($isCurrent ? 'bg-brand' : 'bg-success') : 'bg-body border-2' }}">
                        @if ($done && ! $isCurrent)
                            <x-icon name="check" class="w-3.5 h-3.5 text-white" />
                        @elseif ($isCurrent)
                            <span class="w-2 h-2 rounded-pill bg-body"></span>
                        @endif
                    </span>

                    <div class="-n1">
                        <p class="fw-medium {{ $done ? 'text-brand-emphasis' : 'text-secondary' }}">
                            {{ $step['label'] }}
                            @if ($isCurrent)
                                <span class="ms-2 fs-xs fw-semibold text-brand bg-brand-subtle px-2 py-1 rounded-pill align-middle">In progress</span>
                            @endif
                        </p>
                        <p class="fs-sm {{ $done ? 'text-secondary' : 'text-secondary' }}">{{ $step['desc'] }}</p>
                        @if ($step['at'])
                            <p class="fs-xs text-secondary mt-1"><x-ist :value="$step['at']" /></p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    @endunless
</div>
