<x-layouts.app title="Services" heading="Services">
    {{-- Plan quota. The cap is on the total, so items added under a bigger
         package still count after a downgrade. --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="fs-sm">
            <span class="text-secondary">Plan usage:</span>
            <span class="fw-semibold {{ $quota['can_add'] ? 'text-body' : 'text-danger' }}">
                {{ $quota['used'] }} / {{ $quota['limit'] }} services
            </span>
            @if ($quota['over'])
                <span class="ms-2 fs-xs text-danger">over your plan limit &mdash; nothing removed, but you cannot add more</span>
            @elseif (! $quota['can_add'])
                <span class="ms-2 fs-xs text-danger">limit reached</span>
            @else
                <span class="ms-2 fs-xs text-secondary">{{ $quota['remaining'] }} left</span>
            @endif
        </div>

        @if ($quota['can_add'])
            <a href="{{ route('vip.services.create') }}" class="btn btn-brand btn-sm">
                + Add Service
            </a>
        @else
            <span class="bg-body-secondary text-secondary fs-sm px-4 py-2 rounded-2"
                  title="Ask your Commission Partner to renew you onto a larger package.">
                + Add Service
            </span>
        @endif
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Price</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Order</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($services as $service)
                    <tr class="border-top">
                        <td class="px-4 py-3 fw-medium">{{ $service->name }} @if($service->is_featured) <span class="fs-xs text-gold px-2 py-1 rounded" style="background-color: color-mix(in srgb, var(--color-gold-400) 20%, transparent)">Featured</span> @endif</td>
                        <td class="px-4 py-3">{{ $service->category ?? '—' }}</td>
                        <td class="px-4 py-3 text-nowrap">
                            @if ($service->hasPrice())
                                <span class="fw-medium">₹{{ number_format($service->sellingPrice(), 2) }}</span>
                                @if ($service->strikeThroughPrice())
                                    <span class="fs-xs text-secondary text-decoration-line-through ms-1">₹{{ number_format($service->strikeThroughPrice(), 2) }}</span>
                                    <span class="fs-xs fw-semibold text-success ms-1">{{ $service->discountPercentage() }}% off</span>
                                @endif
                            @else
                                <span class="text-secondary">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded fs-xs {{ $service->status === 'published' ? 'bg-success-subtle text-success' : 'bg-body-secondary text-body-secondary' }}">
                                {{ ucfirst($service->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $service->sort_order }}</td>
                        <td class="px-4 py-3 text-end d-flex align-items-center gap-3">
                            <a href="{{ route('vip.services.edit', $service) }}" class="text-primary hover-underline">Edit</a>
                            <form action="{{ route('vip.services.destroy', $service) }}" method="POST" class="d-inline" data-confirm="Delete this service?" data-confirm-danger>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link p-0 text-decoration-none text-danger hover-underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-secondary">No services yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
