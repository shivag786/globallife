@if (session('status') || session('error'))
    @php $isError = (bool) session('error'); @endphp
    <div data-flash class="fixed top-4 left-1/2 -translate-x-1/2 z-[60] w-[92%] max-w-md transition-opacity duration-300">
        <div class="flex items-start gap-3 rounded-xl px-4 py-3 shadow-lg border {{ $isError ? 'bg-red-50 border-red-200 text-red-800' : 'bg-green-50 border-green-200 text-green-800' }}">
            <x-icon name="{{ $isError ? 'x-mark' : 'check-circle' }}" class="w-5 h-5 flex-shrink-0 mt-0.5" />
            <p class="text-sm flex-1">{{ session('error') ?: session('status') }}</p>
            <button type="button" data-flash-close class="flex-shrink-0 opacity-60 hover:opacity-100" aria-label="Dismiss">
                <x-icon name="x-mark" class="w-4 h-4" />
            </button>
        </div>
    </div>
@endif
