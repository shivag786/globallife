@csrf
@isset($post) @method('PUT') @endisset

<div class="d-flex flex-column gap-4 mw-2xl">
    <div>
        <label for="title" class="d-block fs-sm fw-medium text-body-secondary">Title</label>
        <input id="title" type="text" name="title" value="{{ old('title', $post->title ?? '') }}" required
               class="mt-1 d-block w-100 rounded-2 shadow-sm">
    </div>

    <div class="d-grid grid-cols-1 grid-cols-sm-2 gap-4">
        <div>
            <label for="category" class="d-block fs-sm fw-medium text-body-secondary">Category</label>
            <input id="category" type="text" name="category" value="{{ old('category', $post->category ?? '') }}"
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>
        <div>
            <label for="tags" class="d-block fs-sm fw-medium text-body-secondary">Tags <span class="text-secondary">(comma separated)</span></label>
            <input id="tags" type="text" name="tags" value="{{ old('tags', isset($post) ? implode(', ', $post->tags ?? []) : '') }}"
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>
    </div>

    <div>
        <label for="excerpt" class="d-block fs-sm fw-medium text-body-secondary">Excerpt</label>
        <input id="excerpt" type="text" name="excerpt" value="{{ old('excerpt', $post->excerpt ?? '') }}"
               class="mt-1 d-block w-100 rounded-2 shadow-sm">
    </div>

    <div>
        <label for="editor-content" class="d-block fs-sm fw-medium text-body-secondary mb-1">Content</label>
        <x-ckeditor name="content" :value="$post->content ?? ''" />
    </div>

    <div>
        <label for="featured_image" class="d-block fs-sm fw-medium text-body-secondary">Featured Image</label>
        @if (isset($post) && $post->featured_image)
            <img src="{{ asset('storage/'.$post->featured_image) }}" alt="" class="h-16 mt-1 mb-2 rounded">
        @endif
        <input id="featured_image" type="file" name="featured_image" accept="image/*" class="mt-1 d-block w-100 fs-sm text-body-secondary">
    </div>

    <div>
        <label for="status" class="d-block fs-sm fw-medium text-body-secondary">Status</label>
        <select id="status" name="status" class="mt-1 d-block w-100 rounded-2">
            @foreach (['draft' => 'Draft', 'published' => 'Published'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $post->status ?? 'draft') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <fieldset class="border rounded-2 p-4">
        <legend class="fs-sm fw-medium text-body-secondary px-1">SEO</legend>
        <div class="d-flex flex-column gap-3">
            <div>
                <label for="meta_title" class="d-block fs-sm text-body-secondary">Meta Title</label>
                <input id="meta_title" type="text" name="meta_title" value="{{ old('meta_title', $post->meta_title ?? '') }}"
                       class="mt-1 d-block w-100 rounded-2 shadow-sm">
            </div>
            <div>
                <label for="meta_description" class="d-block fs-sm text-body-secondary">Meta Description</label>
                <input id="meta_description" type="text" name="meta_description" value="{{ old('meta_description', $post->meta_description ?? '') }}"
                       class="mt-1 d-block w-100 rounded-2 shadow-sm">
            </div>
            <div>
                <label for="canonical_url" class="d-block fs-sm text-body-secondary">Canonical URL</label>
                <input id="canonical_url" type="text" name="canonical_url" value="{{ old('canonical_url', $post->canonical_url ?? '') }}"
                       class="mt-1 d-block w-100 rounded-2 shadow-sm">
            </div>
        </div>
    </fieldset>

    <button type="submit" class="btn btn-primary btn-sm">
        {{ isset($post) ? 'Update Post' : 'Create Post' }}
    </button>
</div>
