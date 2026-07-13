<x-layouts.app title="Reviews" heading="Reviews">
    <div class="space-y-3">
        @forelse ($reviews as $review)
            <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-4">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <p class="font-medium">{{ $review->customer_name }}</p>
                        <div class="flex gap-0.5">
                            @for ($i = 1; $i <= 5; $i++)
                                <x-icon name="star" :filled="true" class="w-4 h-4 {{ $i <= $review->rating ? 'text-gold-500' : 'text-slate-200' }}" />
                            @endfor
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded text-xs
                        {{ $review->status === 'approved' ? 'bg-green-50 text-green-700' : ($review->status === 'rejected' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700') }}">
                        {{ ucfirst($review->status) }}
                    </span>
                </div>
                <p class="text-sm text-slate-600 mb-3">{{ $review->review_text }}</p>
                @if ($review->status === 'pending')
                    <div class="space-x-3">
                        <form action="{{ route('vip.reviews.approve', $review) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-green-700 hover:underline text-xs font-medium">Approve</button>
                        </form>
                        <form action="{{ route('vip.reviews.reject', $review) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-red-600 hover:underline text-xs font-medium">Reject</button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-slate-400 text-sm">No reviews yet.</p>
        @endforelse
    </div>
</x-layouts.app>
