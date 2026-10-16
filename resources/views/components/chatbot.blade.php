@php
    $services = app(\App\Repositories\ProductRepository::class)->featured(6)
        ->map(fn ($product) => ['label' => $product->name, 'url' => route('products.show', $product)])
        ->push(['label' => 'Something else', 'url' => null]);
@endphp

<div id="chatbot-root" data-services="{{ $services->toJson() }}" data-enquiry-url="{{ route('enquiry.store') }}" data-csrf="{{ csrf_token() }}">
    <button type="button" id="chatbot-toggle"
            class="btn btn-link p-0 text-decoration-none position-fixed bottom-6 start-6 z-3 w-14 h-14 rounded-pill bg-brand text-white d-flex align-items-center justify-content-center shadow"
            title="Chat with us" aria-label="Open chat assistant">
        <x-icon name="chat-left" class="w-7 h-7" />
    </button>

    <div id="chatbot-panel" class="d-none position-fixed bottom-24 start-6 z-3 mw-sm bg-body rounded-4 shadow-lg border d-flex flex-column overflow-hidden" style="height: 70vh; max-height: 520px;">
        <div class="bg-brand text-white px-4 py-3 d-flex align-items-center justify-content-between flex-shrink-0">
            <div>
                <p class="fw-semibold fs-sm">Global Life Assistant</p>
                <p class="fs-xs text-brand">Usually replies instantly</p>
            </div>
            <button type="button" id="chatbot-close" class="btn btn-link p-0 text-decoration-none text-brand" aria-label="Close chat">
                <x-icon name="x-mark" class="w-5 h-5" />
            </button>
        </div>

        <div id="chatbot-messages" class="flex-grow-1 overflow-y-auto p-4 d-flex flex-column gap-3 fs-sm bg-cream"></div>

        <form id="chatbot-form" data-no-loader="true" class="border-top p-3 d-flex gap-2 flex-shrink-0">
            <input type="text" id="chatbot-input" autocomplete="off"
                   class="flex-grow-1 rounded-pill fs-sm"
                   placeholder="Type your message...">
            <button type="submit" class="btn btn-brand btn-sm rounded-pill">
                Send
            </button>
        </form>
    </div>
</div>
