<x-layouts.app title="FAQs" heading="FAQs">
    <div class="bg-body rounded-2 shadow-sm border p-5 mb-6">
        <h2 class="fw-semibold mb-3">Add FAQ</h2>
        <form method="POST" action="{{ route('vip.faqs.store') }}" class="d-flex flex-column gap-3">
            @csrf
            <input type="text" name="question" placeholder="Question" required class="d-block w-100 rounded-2 shadow-sm fs-sm">
            <textarea name="answer" placeholder="Answer" rows="2" required class="d-block w-100 rounded-2 shadow-sm fs-sm"></textarea>
            <button type="submit" class="btn btn-brand btn-sm">Add FAQ</button>
        </form>
    </div>

    <div class="d-flex flex-column gap-3">
        @forelse ($faqs as $faq)
            <div class="bg-body rounded-2 shadow-sm border p-4">
                <form method="POST" action="{{ route('vip.faqs.update', $faq) }}" class="d-flex flex-column gap-2">
                    @csrf
                    @method('PUT')
                    <input type="text" name="question" value="{{ $faq->question }}" class="d-block w-100 rounded-2 shadow-sm fs-sm fw-medium">
                    <textarea name="answer" rows="2" class="d-block w-100 rounded-2 shadow-sm fs-sm">{{ $faq->answer }}</textarea>
                    <div class="d-flex align-items-center justify-content-between">
                        <label class="d-flex align-items-center gap-2 fs-xs">
                            <input type="checkbox" name="is_visible" value="1" @checked($faq->is_visible)>
                            Visible
                        </label>
                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-link p-0 text-decoration-none text-primary hover-underline fs-xs">Save</button>
                        </div>
                    </div>
                </form>
                <form method="POST" action="{{ route('vip.faqs.destroy', $faq) }}" data-confirm="Delete this FAQ?" data-confirm-danger class="mt-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-link p-0 text-decoration-none text-danger hover-underline fs-xs">Delete</button>
                </form>
            </div>
        @empty
            <p class="text-secondary fs-sm">No FAQs yet.</p>
        @endforelse
    </div>
</x-layouts.app>
