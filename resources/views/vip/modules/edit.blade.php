<x-layouts.app title="Section Visibility" heading="Manage Section Visibility">
    <p class="text-sm text-slate-500 mb-6">Turn sections on or off for your public business page. Hidden sections stay saved &mdash; you can re-enable them anytime.</p>

    <form method="POST" action="{{ route('vip.modules.update') }}" class="max-w-2xl">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-5 mb-6">
            <h2 class="font-semibold mb-4">Page Sections</h2>
            <div class="space-y-3">
                @foreach ($sections as $key => $label)
                    <label class="flex items-center justify-between border-b border-slate-100 pb-3 last:border-b-0 last:pb-0">
                        <span class="text-sm text-slate-700">{{ $label }}</span>
                        <input type="checkbox" name="modules[{{ $key }}]" value="1" class="w-5 h-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500" @checked($current[$key] ?? true)>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-5 mb-6">
            <h2 class="font-semibold mb-4">Floating Buttons</h2>
            <div class="space-y-3">
                @foreach ($floatingButtons as $key => $label)
                    <label class="flex items-center justify-between border-b border-slate-100 pb-3 last:border-b-0 last:pb-0">
                        <span class="text-sm text-slate-700">{{ $label }}</span>
                        <input type="checkbox" name="modules[{{ $key }}]" value="1" class="w-5 h-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500" @checked($current[$key] ?? true)>
                    </label>
                @endforeach
            </div>
        </div>

        <button type="submit" class="bg-brand-700 text-white text-sm px-5 py-2.5 rounded-md hover:bg-brand-800 transition font-medium">
            Save Visibility
        </button>
    </form>
</x-layouts.app>
