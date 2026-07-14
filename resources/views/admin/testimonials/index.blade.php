<x-layouts.app title="Testimonials" heading="Testimonials">
    <div class="flex justify-end mb-4">
        @can('testimonials.create')
            <a href="{{ route('admin.testimonials.create') }}" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">
                + Add Testimonial
            </a>
        @endcan
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
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
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium">{{ $testimonial->name }}</td>
                        <td class="px-4 py-3">{{ $testimonial->city }}</td>
                        <td class="px-4 py-3">{{ str_repeat('★', $testimonial->rating) }}{{ str_repeat('☆', 5 - $testimonial->rating) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs {{ $testimonial->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $testimonial->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            @can('testimonials.edit')
                                <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="text-indigo-600 hover:underline">Edit</a>
                            @endcan
                            @can('testimonials.delete')
                                <form action="{{ route('admin.testimonials.destroy', $testimonial) }}" method="POST" class="inline" data-confirm="Delete this testimonial?" data-confirm-danger>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">No testimonials yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
