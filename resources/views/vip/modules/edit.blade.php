<x-layouts.app title="Section Visibility" heading="Manage Section Visibility">
    <p class="fs-sm text-secondary mb-6">Turn sections on or off for your public business page. Hidden sections stay saved &mdash; you can re-enable them anytime.</p>

    <form method="POST" action="{{ route('vip.modules.update') }}" class="mw-2xl">
        @csrf
        @method('PUT')

        <div class="bg-body rounded-2 shadow-sm border p-5 mb-6">
            <h2 class="fw-semibold mb-4">Page Sections</h2>
            <div class="d-flex flex-column gap-3">
                @foreach ($sections as $key => $label)
                    <label class="d-flex align-items-center justify-content-between border-bottom pb-3">
                        <span class="fs-sm text-body-secondary">{{ $label }}</span>
                        <input type="checkbox" name="modules[{{ $key }}]" value="1" class="w-5 h-5 rounded text-brand" @checked($current[$key] ?? true)>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="bg-body rounded-2 shadow-sm border p-5 mb-6">
            <h2 class="fw-semibold mb-4">Floating Buttons</h2>
            <div class="d-flex flex-column gap-3">
                @foreach ($floatingButtons as $key => $label)
                    <label class="d-flex align-items-center justify-content-between border-bottom pb-3">
                        <span class="fs-sm text-body-secondary">{{ $label }}</span>
                        <input type="checkbox" name="modules[{{ $key }}]" value="1" class="w-5 h-5 rounded text-brand" @checked($current[$key] ?? true)>
                    </label>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-brand btn-sm">
            Save Visibility
        </button>
    </form>
</x-layouts.app>
