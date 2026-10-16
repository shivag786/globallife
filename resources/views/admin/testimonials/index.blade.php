<x-layouts.app title="Testimonials" heading="Testimonials">
    <div class="d-flex justify-content-end mb-4">
        @can('testimonials.create')
            <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary btn-sm">
                + Add Testimonial
            </a>
        @endcan
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">City</th>
                    <th class="px-4 py-3">Rating</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($testimonials as $testimonial)
                    <tr class="border-top">
                        <td class="px-4 py-3 fw-medium">{{ $testimonial->name }}</td>
                        <td class="px-4 py-3">{{ $testimonial->city }}</td>
                        <td class="px-4 py-3">{{ str_repeat('★', $testimonial->rating) }}{{ str_repeat('☆', 5 - $testimonial->rating) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded fs-xs {{ $testimonial->status === 'active' ? 'bg-success-subtle text-success' : 'bg-body-secondary text-secondary' }}">
                                {{ $testimonial->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-end d-flex align-items-center gap-3">
                            @can('testimonials.edit')
                                <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="text-primary hover-underline">Edit</a>
                            @endcan
                            @can('testimonials.delete')
                                <form action="{{ route('admin.testimonials.destroy', $testimonial) }}" method="POST" class="d-inline" data-confirm="Delete this testimonial?" data-confirm-danger>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-link p-0 text-decoration-none text-danger hover-underline">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-secondary">No testimonials yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
