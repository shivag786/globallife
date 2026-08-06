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

<div class="bg-white border border-slate-100 rounded-2xl overflow-hidden">
    {{-- Headline: arriving-by / delivered / cancelled --}}
    @if ($order->isCancelled())
        <div class="flex items-center gap-3 px-5 sm:px-6 py-4 bg-red-50 border-b border-red-100">
            <x-icon name="x-mark" class="w-6 h-6 text-red-600 flex-shrink-0" />
            <div>
                <p class="font-semibold text-red-700 capitalize">Order {{ $order->status }}</p>
                <p class="text-sm text-red-600/80">This order was {{ $order->status }} and will not be delivered.</p>
            </div>
        </div>
    @elseif ($order->isDelivered())
        <div class="flex items-center gap-3 px-5 sm:px-6 py-4 bg-green-50 border-b border-green-100">
            <x-icon name="check-circle" class="w-6 h-6 text-green-600 flex-shrink-0" />
            <div>
                <p class="font-semibold text-green-700">Delivered</p>
                <p class="text-sm text-green-600/80">Delivered on <x-ist :value="$order->delivered_at" format="d M Y" :suffix="''" /></p>
            </div>
        </div>
    @else
        <div class="flex items-center gap-3 px-5 sm:px-6 py-4 bg-brand-50 border-b border-brand-100">
            <x-icon name="truck" class="w-6 h-6 text-brand-700 flex-shrink-0" />
            <div>
                <p class="font-semibold text-brand-900">Arriving by
                    <span class="text-brand-700">{{ optional($expected)->timezone('Asia/Kolkata')->format('D, d M Y') ?? 'soon' }}</span>
                </p>
                <p class="text-sm text-slate-500">We’ll keep this updated as your order moves.</p>
            </div>
        </div>
    @endif

    {{-- Vertical stepper --}}
    @unless ($order->isCancelled())
        <ol class="px-5 sm:px-6 py-5">
            @foreach ($steps as $i => $step)
                @php
                    $stepRank = \App\Models\Order::STATUS_RANK[$step['key']] ?? 0;
                    $done = $rank >= $stepRank;
                    $isCurrent = $order->status === $step['key'] && ! $order->isDelivered();
                    $isLast = $i === count($steps) - 1;
                @endphp
                <li class="flex gap-4 {{ $isLast ? '' : 'pb-6' }} relative">
                    {{-- Connector line --}}
                    @unless ($isLast)
                        <span class="absolute left-[11px] top-6 bottom-0 w-0.5 {{ $rank > $stepRank ? 'bg-green-500' : 'bg-slate-200' }}"></span>
                    @endunless

                    {{-- Dot --}}
                    <span class="relative z-10 flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center
                        {{ $done ? ($isCurrent ? 'bg-brand-600 ring-4 ring-brand-100' : 'bg-green-500') : 'bg-white border-2 border-slate-300' }}">
                        @if ($done && ! $isCurrent)
                            <x-icon name="check" class="w-3.5 h-3.5 text-white" />
                        @elseif ($isCurrent)
                            <span class="w-2 h-2 rounded-full bg-white"></span>
                        @endif
                    </span>

                    <div class="-mt-0.5">
                        <p class="font-medium {{ $done ? 'text-brand-900' : 'text-slate-400' }}">
                            {{ $step['label'] }}
                            @if ($isCurrent)
                                <span class="ml-1.5 text-xs font-semibold text-brand-700 bg-brand-100 px-2 py-0.5 rounded-full align-middle">In progress</span>
                            @endif
                        </p>
                        <p class="text-sm {{ $done ? 'text-slate-500' : 'text-slate-400' }}">{{ $step['desc'] }}</p>
                        @if ($step['at'])
                            <p class="text-xs text-slate-400 mt-0.5"><x-ist :value="$step['at']" /></p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    @endunless
</div>
