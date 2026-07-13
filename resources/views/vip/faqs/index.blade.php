<x-layouts.app title="FAQs" heading="FAQs">
    <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-5 mb-6">
        <h2 class="font-semibold mb-3">Add FAQ</h2>
        <form method="POST" action="{{ route('vip.faqs.store') }}" class="space-y-3">
            @csrf
            <input type="text" name="question" placeholder="Question" required class="block w-full rounded-md border-slate-300 shadow-sm text-sm">
            <textarea name="answer" placeholder="Answer" rows="2" required class="block w-full rounded-md border-slate-300 shadow-sm text-sm"></textarea>
            <button type="submit" class="bg-brand-700 text-white text-sm px-4 py-2 rounded-md hover:bg-brand-800">Add FAQ</button>
        </form>
    </div>

    <div class="space-y-3">
        @forelse ($faqs as $faq)
            <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-4">
                <form method="POST" action="{{ route('vip.faqs.update', $faq) }}" class="space-y-2">
                    @csrf
                    @method('PUT')
                    <input type="text" name="question" value="{{ $faq->question }}" class="block w-full rounded-md border-slate-300 shadow-sm text-sm font-medium">
                    <textarea name="answer" rows="2" class="block w-full rounded-md border-slate-300 shadow-sm text-sm">{{ $faq->answer }}</textarea>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-1.5 text-xs">
                            <input type="checkbox" name="is_visible" value="1" @checked($faq->is_visible)>
                            Visible
                        </label>
                        <div class="space-x-3">
                            <button type="submit" class="text-indigo-600 hover:underline text-xs">Save</button>
                        </div>
                    </div>
                </form>
                <form method="POST" action="{{ route('vip.faqs.destroy', $faq) }}" onsubmit="return confirm('Delete this FAQ?')" class="mt-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:underline text-xs">Delete</button>
                </form>
            </div>
        @empty
            <p class="text-slate-400 text-sm">No FAQs yet.</p>
        @endforelse
    </div>
</x-layouts.app>
