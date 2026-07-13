@csrf
@isset($post) @method('PUT') @endisset

<div class="space-y-4 max-w-2xl">
    <div>
        <label for="title" class="block text-sm font-medium text-slate-700">Title</label>
        <input id="title" type="text" name="title" value="{{ old('title', $post->title ?? '') }}" required
               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="category" class="block text-sm font-medium text-slate-700">Category</label>
            <input id="category" type="text" name="category" value="{{ old('category', $post->category ?? '') }}"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="tags" class="block text-sm font-medium text-slate-700">Tags <span class="text-slate-400">(comma separated)</span></label>
            <input id="tags" type="text" name="tags" value="{{ old('tags', isset($post) ? implode(', ', $post->tags ?? []) : '') }}"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <div>
        <label for="excerpt" class="block text-sm font-medium text-slate-700">Excerpt</label>
        <input id="excerpt" type="text" name="excerpt" value="{{ old('excerpt', $post->excerpt ?? '') }}"
               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label for="editor-content" class="block text-sm font-medium text-slate-700 mb-1">Content</label>
        <x-ckeditor name="content" :value="$post->content ?? ''" />
    </div>

    <div>
        <label for="featured_image" class="block text-sm font-medium text-slate-700">Featured Image</label>
        @if (isset($post) && $post->featured_image)
            <img src="{{ asset('storage/'.$post->featured_image) }}" alt="" class="h-16 mt-1 mb-2 rounded">
        @endif
        <input id="featured_image" type="file" name="featured_image" accept="image/*" class="mt-1 block w-full text-sm text-slate-600">
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
        <select id="status" name="status" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach (['draft' => 'Draft', 'published' => 'Published'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $post->status ?? 'draft') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <fieldset class="border border-slate-200 rounded-md p-4">
        <legend class="text-sm font-medium text-slate-700 px-1">SEO</legend>
        <div class="space-y-3">
            <div>
                <label for="meta_title" class="block text-sm text-slate-600">Meta Title</label>
                <input id="meta_title" type="text" name="meta_title" value="{{ old('meta_title', $post->meta_title ?? '') }}"
                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="meta_description" class="block text-sm text-slate-600">Meta Description</label>
                <input id="meta_description" type="text" name="meta_description" value="{{ old('meta_description', $post->meta_description ?? '') }}"
                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="canonical_url" class="block text-sm text-slate-600">Canonical URL</label>
                <input id="canonical_url" type="text" name="canonical_url" value="{{ old('canonical_url', $post->canonical_url ?? '') }}"
                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>
    </fieldset>

    <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">
        {{ isset($post) ? 'Update Post' : 'Create Post' }}
    </button>
</div>
