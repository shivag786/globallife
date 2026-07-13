@php
    $services = app(\App\Repositories\ProductRepository::class)->featured(6)
        ->map(fn ($product) => ['label' => $product->name, 'url' => route('products.show', $product)])
        ->push(['label' => 'VIP Membership', 'url' => route('vip-plans.index')])
        ->push(['label' => 'Something else', 'url' => null]);
@endphp

<div id="chatbot-root" data-services="{{ $services->toJson() }}" data-enquiry-url="{{ route('enquiry.store') }}" data-csrf="{{ csrf_token() }}">
    <button type="button" id="chatbot-toggle"
            class="fixed bottom-6 left-6 z-40 w-14 h-14 rounded-full bg-brand-700 text-white flex items-center justify-center shadow-lg hover:bg-brand-800 hover:scale-110 transition"
            title="Chat with us" aria-label="Open chat assistant">
        <x-icon name="chat-left" class="w-7 h-7" />
    </button>

    <div id="chatbot-panel" class="hidden fixed bottom-24 left-6 z-40 w-[90vw] max-w-sm bg-white rounded-2xl shadow-2xl border border-slate-100 flex flex-col overflow-hidden" style="height: 70vh; max-height: 520px;">
        <div class="bg-brand-700 text-white px-4 py-3 flex items-center justify-between flex-shrink-0">
            <div>
                <p class="font-semibold text-sm">Global Life Assistant</p>
                <p class="text-xs text-brand-100">Usually replies instantly</p>
            </div>
            <button type="button" id="chatbot-close" class="text-brand-100 hover:text-white" aria-label="Close chat">
                <x-icon name="x-mark" class="w-5 h-5" />
            </button>
        </div>

        <div id="chatbot-messages" class="flex-1 overflow-y-auto p-4 space-y-3 text-sm bg-cream"></div>

        <form id="chatbot-form" data-no-loader="true" class="border-t border-slate-100 p-3 flex gap-2 flex-shrink-0">
            <input type="text" id="chatbot-input" autocomplete="off"
                   class="flex-1 rounded-full border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500"
                   placeholder="Type your message...">
            <button type="submit" class="bg-brand-700 text-white px-4 rounded-full text-sm font-medium hover:bg-brand-800">
                Send
            </button>
        </form>
    </div>
</div>
