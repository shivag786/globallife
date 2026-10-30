<x-layouts.app :heading="'Page Builder'" :title="'Page Builder — '.$microsite->business_name">
    {{-- The editor is rendered by resources/js/vip/page-builder.js from the JSON
         below; this view only provides the frame, the help, and the toolbar. --}}
    <div data-page-builder
         data-save-url="{{ route('vip.page-builder.update') }}"
         data-upload-url="{{ route('vip.page-builder.upload') }}">

        {{-- Sticky save bar: turns amber as soon as anything changes. --}}
        <div class="pb-toolbar position-sticky top-0 bg-body border rounded-4 px-4 py-3 mb-4 d-flex flex-wrap align-items-center gap-3 shadow-sm">
            <div class="flex-grow-1 min-w-0">
                <p class="fw-semibold mb-0 text-truncate">{{ $microsite->business_name }}</p>
                <p class="fs-xs mb-0 d-flex align-items-center gap-2" data-pb-status>
                    <span class="pb-status-dot"></span>
                    <span data-pb-status-text>All changes saved</span>
                </p>
            </div>
            <a href="{{ url($microsite->publicPath()) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2">
                <x-icon name="eye" class="w-4 h-4" /> View my page
            </a>
            <button type="button" class="btn btn-sm btn-link text-secondary d-none" data-pb-discard>Discard changes</button>
            <button type="button" class="btn btn-sm pb-save-btn d-inline-flex align-items-center gap-2" data-pb-save disabled>
                <x-icon name="check" class="w-4 h-4" /> <span data-pb-save-label>Saved</span>
            </button>
        </div>

        <details class="bg-body border rounded-4 mb-4" @if (! $microsite->page_layout) open @endif>
            <summary class="px-4 py-3 fw-semibold d-flex align-items-center justify-content-between">
                <span>How to edit your page</span>
                <x-icon name="chevron-down" class="w-4 h-4 text-secondary" />
            </summary>
            <div class="px-4 pb-4 d-grid grid-cols-md-2 grid-cols-xl-3 gap-3 fs-sm">
                @foreach ([
                    ['⠿', 'Move things', 'Hold the dotted handle and drag a section up or down, or drag a card left or right.'],
                    ['Aa', 'Change any text', 'Click on a heading, label, price or number and type. What you see is how it looks on your page.'],
                    ['📷', 'Add a photo', 'Click the photo area, choose a picture, then drag and zoom to crop it.'],
                    ['👁', 'Show or hide', 'Switch a section or card off to hide it from customers without deleting it.'],
                    ['🗑', 'Delete', 'The bin icon removes a card. You will be asked to confirm first.'],
                    ['💾', 'Save', 'Nothing goes live until you press Save changes. The button turns orange when you have unsaved work.'],
                ] as [$icon, $title, $text])
                    <div class="d-flex gap-3 p-3 rounded-3 bg-body-tertiary">
                        <span class="w-9 h-9 rounded-pill bg-brand-subtle text-brand fw-bold d-flex align-items-center justify-content-center flex-shrink-0">{{ $icon }}</span>
                        <div>
                            <p class="fw-semibold mb-1">{{ $title }}</p>
                            <p class="text-secondary mb-0">{{ $text }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </details>

        <div class="alert alert-danger small d-none" data-pb-errors></div>

        {{-- Hero banner is always first on the page; it has its own screen. --}}
        <div class="pb-pinned border border-dashed rounded-4 px-4 py-3 mb-4 d-flex align-items-center gap-3">
            <x-icon name="photo" class="w-5 h-5 text-secondary" />
            <div class="flex-grow-1">
                <p class="fw-semibold mb-0">Top Banner <span class="badge text-bg-light border ms-1">always first</span></p>
                <p class="fs-xs text-secondary mb-0">{{ $microsite->banners()->count() }} banner slide(s). The big picture visitors see first.</p>
            </div>
            <a href="{{ route('vip.banners.index') }}" class="btn btn-sm btn-outline-brand">Edit banners</a>
        </div>

        <div data-pb-sections></div>

        <button type="button" class="pb-add-section w-100 border border-2 border-dashed rounded-4 py-5 mb-4 bg-transparent text-brand fw-semibold d-flex flex-column align-items-center gap-1" data-pb-add-section>
            <x-icon name="plus" class="w-6 h-6" />
            Add a new section
            <span class="fs-xs fw-normal text-secondary">e.g. Meet Our Doctors, Facilities, Our Team, Offers</span>
        </button>

        <div class="pb-pinned border border-dashed rounded-4 px-4 py-3 d-flex align-items-center gap-3">
            <x-icon name="envelope" class="w-5 h-5 text-secondary" />
            <div class="flex-grow-1">
                <p class="fw-semibold mb-0">Enquiry form <span class="badge text-bg-light border ms-1">always last</span></p>
                <p class="fs-xs text-secondary mb-0">Messages from this form arrive in Leads.</p>
            </div>
        </div>
    </div>

    <script id="page-builder-state" type="application/json">@json($state)</script>
</x-layouts.app>
