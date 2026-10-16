<x-layouts.app title="Reviews" heading="Reviews">
    <div class="d-flex flex-column gap-3">
        @forelse ($reviews as $review)
            <div class="bg-body rounded-2 shadow-sm border p-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <p class="fw-medium">{{ $review->customer_name }}</p>
                        <div class="d-flex gap-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <x-icon name="star" :filled="true" class="w-4 h-4 {{ $i <= $review->rating ? 'text-gold' : 'text-body-tertiary' }}" />
                            @endfor
                        </div>
                    </div>
                    <span class="px-2 py-1 rounded fs-xs {{ $review->status === 'approved' ? 'bg-success-subtle text-success' : ($review->status === 'rejected' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning') }}">
                        {{ ucfirst($review->status) }}
                    </span>
                </div>
                <p class="fs-sm text-body-secondary mb-3">{{ $review->review_text }}</p>
                @if ($review->status === 'pending')
                    <div class="d-flex align-items-center gap-3">
                        <form action="{{ route('vip.reviews.approve', $review) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-link p-0 text-decoration-none text-success hover-underline fs-xs fw-medium">Approve</button>
                        </form>
                        <form action="{{ route('vip.reviews.reject', $review) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-link p-0 text-decoration-none text-danger hover-underline fs-xs fw-medium">Reject</button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-secondary fs-sm">No reviews yet.</p>
        @endforelse
    </div>
</x-layouts.app>
