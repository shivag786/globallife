@if (session('status') || session('error'))
    @php $isError = (bool) session('error'); @endphp
    {{-- Fixed, centred toast. Dismissed by its own button or after a few
         seconds by the [data-flash] handler in app.js. --}}
    <div data-flash class="position-fixed top-0 start-50 translate-middle-x mt-3 w-100 px-3"
         style="z-index: 1080; max-width: 28rem; transition: opacity 300ms ease">
        <div class="alert {{ $isError ? 'alert-danger' : 'alert-success' }} shadow d-flex align-items-start gap-2 mb-0">
            <x-icon name="{{ $isError ? 'x-mark' : 'check-circle' }}" class="w-5 h-5 flex-shrink-0 mt-1" />
            <p class="small flex-grow-1 mb-0">{{ session('error') ?: session('status') }}</p>
            <button type="button" data-flash-close class="btn-close btn-close-sm flex-shrink-0"
                    aria-label="Dismiss"></button>
        </div>
    </div>
@endif
