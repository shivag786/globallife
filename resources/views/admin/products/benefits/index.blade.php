<x-layouts.app :title="'Benefits — '.$product->name" heading="Product Benefits">
    @php
        $iconOptions = ['leaf', 'heart', 'shield-check', 'check-circle', 'sparkles', 'beaker', 'star', 'truck', 'sun', 'users', 'academic-cap', 'tag', 'map-pin'];
        $isEditing = (bool) $editing;
        $formAction = $isEditing
            ? route('admin.products.benefits.update', [$product, $editing])
            : route('admin.products.benefits.store', $product);
    @endphp

    <div class="d-flex align-items-center justify-content-between mb-4">
        <p class="fs-sm text-secondary">
            Manage the benefits shown in the product's “Benefits” popup for
            <span class="fw-medium text-body-secondary">{{ $product->name }}</span>.
        </p>
        <a href="{{ route('admin.products.edit', $product) }}" class="fs-sm text-primary hover-underline">← Back to product</a>
    </div>

    <div class="d-grid grid-template-lg gap-6 align-items-start" style="--cols-lg: 380px minmax(0, 1fr)">
        {{-- Add / Edit form --}}
        <div class="bg-body rounded-2 shadow-sm border p-5">
            <h2 class="fw-semibold text-body mb-4">{{ $isEditing ? 'Edit Benefit' : 'Add Benefit' }}</h2>
            <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="d-flex flex-column gap-4">
                @csrf
                @if ($isEditing) @method('PUT') @endif

                <div>
                    <label for="title" class="d-block fs-sm fw-medium text-body-secondary">Title</label>
                    <input id="title" type="text" name="title" value="{{ old('title', $editing->title ?? '') }}" required
                           class="mt-1 d-block w-100 rounded-2 shadow-sm">
                </div>

                <div>
                    <label for="description" class="d-block fs-sm fw-medium text-body-secondary">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="mt-1 d-block w-100 rounded-2 shadow-sm">{{ old('description', $editing->description ?? '') }}</textarea>
                </div>

                <div>
                    <label for="icon" class="d-block fs-sm fw-medium text-body-secondary">Icon</label>
                    <select id="icon" name="icon" class="mt-1 d-block w-100 rounded-2 shadow-sm">
                        <option value="">— No icon —</option>
                        @foreach ($iconOptions as $ic)
                            <option value="{{ $ic }}" @selected(old('icon', $editing->icon ?? '') === $ic)>{{ $ic }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="image" class="d-block fs-sm fw-medium text-body-secondary">Image <span class="text-secondary">(optional, overrides icon)</span></label>
                    @if ($isEditing && $editing->image)
                        <img src="{{ asset('storage/'.$editing->image) }}" alt="" class="h-14 mt-1 mb-2 rounded">
                    @endif
                    <input id="image" type="file" name="image" accept="image/*" class="mt-1 d-block w-100 fs-sm text-body-secondary">
                </div>

                <div class="d-grid grid-cols-2 gap-3">
                    <div>
                        <label for="display_order" class="d-block fs-sm fw-medium text-body-secondary">Order</label>
                        <input id="display_order" type="number" min="0" name="display_order" value="{{ old('display_order', $editing->display_order ?? 0) }}"
                               class="mt-1 d-block w-100 rounded-2 shadow-sm">
                    </div>
                    <div>
                        <label for="status" class="d-block fs-sm fw-medium text-body-secondary">Status</label>
                        <select id="status" name="status" class="mt-1 d-block w-100 rounded-2 shadow-sm">
                            @foreach (['active', 'inactive'] as $option)
                                <option value="{{ $option }}" @selected(old('status', $editing->status ?? 'active') === $option)>{{ ucfirst($option) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        {{ $isEditing ? 'Update Benefit' : 'Add Benefit' }}
                    </button>
                    @if ($isEditing)
                        <a href="{{ route('admin.products.benefits.index', $product) }}" class="fs-sm text-secondary hover-underline">Cancel</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Existing benefits --}}
        <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
            <div class="px-5 py-3 border-bottom fs-sm fw-medium text-body-secondary">
                {{ $benefits->count() }} {{ Str::plural('benefit', $benefits->count()) }}
            </div>
            <ul class="divide-y">
                @forelse ($benefits as $benefit)
                    <li class="d-flex align-items-start gap-4 px-5 py-4 {{ $editing && $editing->id === $benefit->id ? 'bg-primary-subtle' : '' }}">
                        <div class="flex-shrink-0 w-11 h-11 rounded-2 bg-brand-subtle d-flex align-items-center justify-content-center overflow-hidden">
                            @if ($benefit->image)
                                <img src="{{ asset('storage/'.$benefit->image) }}" alt="" class="w-100 h-100 object-fit-cover">
                            @elseif ($benefit->icon)
                                <x-icon :name="$benefit->icon" class="w-6 h-6 text-brand" />
                            @else
                                <x-icon name="sparkles" class="w-6 h-6 text-body-tertiary" />
                            @endif
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="d-flex align-items-center gap-2">
                                <p class="fw-medium text-body">{{ $benefit->title }}</p>
                                <span class="fs-xs text-secondary">#{{ $benefit->display_order }}</span>
                                <span class="px-2 py-1 rounded fs-xs {{ $benefit->status === 'active' ? 'bg-success-subtle text-success' : 'bg-body-secondary text-secondary' }}">{{ $benefit->status }}</span>
                            </div>
                            @if ($benefit->description)
                                <p class="fs-sm text-secondary mt-1">{{ $benefit->description }}</p>
                            @endif
                        </div>
                        <div class="flex-shrink-0 d-flex align-items-center gap-3 fs-sm">
                            <a href="{{ route('admin.products.benefits.index', [$product, 'edit' => $benefit->id]) }}" class="text-primary hover-underline">Edit</a>
                            <form action="{{ route('admin.products.benefits.destroy', [$product, $benefit]) }}" method="POST" class="d-inline" data-confirm="Delete this benefit?" data-confirm-danger>
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-link p-0 text-decoration-none text-danger hover-underline">Delete</button>
                            </form>
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-10 text-center text-secondary fs-sm">No benefits yet. Add the first one on the left.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-layouts.app>
