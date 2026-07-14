<x-layouts.app title="Blog" heading="Blog">
    <div class="flex justify-end mb-4">
        @can('blog.create')
            <a href="{{ route('admin.blog.create') }}" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">
                + Add Post
            </a>
        @endcan
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Author</th>
                    <th class="px-4 py-3">Views</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($posts as $post)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium">{{ $post->title }}</td>
                        <td class="px-4 py-3">{{ $post->author?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $post->views }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs {{ $post->status === 'published' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $post->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            @if ($post->status === 'published')
                                <a href="{{ route('blog.show', $post) }}" class="text-slate-500 hover:underline" target="_blank">View</a>
                            @endif
                            @can('blog.edit')
                                <a href="{{ route('admin.blog.edit', $post) }}" class="text-indigo-600 hover:underline">Edit</a>
                            @endcan
                            @can('blog.delete')
                                <form action="{{ route('admin.blog.destroy', $post) }}" method="POST" class="inline" data-confirm="Delete this post?" data-confirm-danger>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">No posts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
