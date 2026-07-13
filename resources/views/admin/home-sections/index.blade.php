<x-layouts.app title="Homepage Builder" heading="Homepage Builder">
    <p class="text-sm text-slate-500 mb-4">
        Sections render on the public homepage top-to-bottom in the order below. Toggle a section off to hide it without deleting it.
    </p>

    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.home-sections.create') }}" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">
            + Add Section
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Order</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sections as $section)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1">
                                {{ $section->display_order }}
                                <form action="{{ route('admin.home-sections.move-up', $section) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-slate-400 hover:text-slate-700" title="Move up">&uarr;</button>
                                </form>
                                <form action="{{ route('admin.home-sections.move-down', $section) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-slate-400 hover:text-slate-700" title="Move down">&darr;</button>
                                </form>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs bg-indigo-50 text-indigo-700 capitalize">{{ $section->type }}</span>
                        </td>
                        <td class="px-4 py-3 font-medium">{{ $section->title }}</td>
                        <td class="px-4 py-3">
                            <form action="{{ route('admin.home-sections.toggle-status', $section) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="px-2 py-0.5 rounded text-xs {{ $section->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $section->status }}
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('admin.home-sections.edit', $section) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.home-sections.destroy', $section) }}" method="POST" class="inline" onsubmit="return confirm('Delete this section?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">No homepage sections yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
