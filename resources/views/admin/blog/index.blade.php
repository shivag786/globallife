<x-layouts.app title="Blog" heading="Blog">
    <div class="d-flex justify-content-end mb-4">
        @can('blog.create')
            <a href="{{ route('admin.blog.create') }}" class="btn btn-primary btn-sm">
                + Add Post
            </a>
        @endcan
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
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
                    <tr class="border-top">
                        <td class="px-4 py-3 fw-medium">{{ $post->title }}</td>
                        <td class="px-4 py-3">{{ $post->author?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $post->views }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded fs-xs {{ $post->status === 'published' ? 'bg-success-subtle text-success' : 'bg-body-secondary text-secondary' }}">
                                {{ $post->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-end d-flex align-items-center gap-3">
                            @if ($post->status === 'published')
                                <a href="{{ route('blog.show', $post) }}" class="text-secondary hover-underline" target="_blank">View</a>
                            @endif
                            @can('blog.edit')
                                <a href="{{ route('admin.blog.edit', $post) }}" class="text-primary hover-underline">Edit</a>
                            @endcan
                            @can('blog.delete')
                                <form action="{{ route('admin.blog.destroy', $post) }}" method="POST" class="d-inline" data-confirm="Delete this post?" data-confirm-danger>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-link p-0 text-decoration-none text-danger hover-underline">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-secondary">No posts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
