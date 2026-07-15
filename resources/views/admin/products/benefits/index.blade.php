<x-layouts.app :title="'Benefits — '.$product->name" heading="Product Benefits">
    @php
        $iconOptions = ['leaf', 'heart', 'shield-check', 'check-circle', 'sparkles', 'beaker', 'star', 'truck', 'sun', 'users', 'academic-cap', 'tag', 'map-pin'];
        $isEditing = (bool) $editing;
        $formAction = $isEditing
            ? route('admin.products.benefits.update', [$product, $editing])
            : route('admin.products.benefits.store', $product);
    @endphp

    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-slate-500">
            Manage the benefits shown in the product's “Benefits” popup for
            <span class="font-medium text-slate-700">{{ $product->name }}</span>.
        </p>
        <a href="{{ route('admin.products.edit', $product) }}" class="text-sm text-indigo-600 hover:underline">← Back to product</a>
    </div>

    <div class="grid lg:grid-cols-[380px_1fr] gap-6 items-start">
        {{-- Add / Edit form --}}
        <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-5">
            <h2 class="font-semibold text-slate-800 mb-4">{{ $isEditing ? 'Edit Benefit' : 'Add Benefit' }}</h2>
            <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @if ($isEditing) @method('PUT') @endif

                <div>
                    <label for="title" class="block text-sm font-medium text-slate-700">Title</label>
                    <input id="title" type="text" name="title" value="{{ old('title', $editing->title ?? '') }}" required
                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $editing->description ?? '') }}</textarea>
                </div>

                <div>
                    <label for="icon" class="block text-sm font-medium text-slate-700">Icon</label>
                    <select id="icon" name="icon" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— No icon —</option>
                        @foreach ($iconOptions as $ic)
                            <option value="{{ $ic }}" @selected(old('icon', $editing->icon ?? '') === $ic)>{{ $ic }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="image" class="block text-sm font-medium text-slate-700">Image <span class="text-slate-400">(optional, overrides icon)</span></label>
                    @if ($isEditing && $editing->image)
                        <img src="{{ asset('storage/'.$editing->image) }}" alt="" class="h-14 mt-1 mb-2 rounded">
                    @endif
                    <input id="image" type="file" name="image" accept="image/*" class="mt-1 block w-full text-sm text-slate-600">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="display_order" class="block text-sm font-medium text-slate-700">Order</label>
                        <input id="display_order" type="number" min="0" name="display_order" value="{{ old('display_order', $editing->display_order ?? 0) }}"
                               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach (['active', 'inactive'] as $option)
                                <option value="{{ $option }}" @selected(old('status', $editing->status ?? 'active') === $option)>{{ ucfirst($option) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">
                        {{ $isEditing ? 'Update Benefit' : 'Add Benefit' }}
                    </button>
                    @if ($isEditing)
                        <a href="{{ route('admin.products.benefits.index', $product) }}" class="text-sm text-slate-500 hover:underline">Cancel</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Existing benefits --}}
        <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 text-sm font-medium text-slate-600">
                {{ $benefits->count() }} {{ Str::plural('benefit', $benefits->count()) }}
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($benefits as $benefit)
                    <li class="flex items-start gap-4 px-5 py-4 {{ $editing && $editing->id === $benefit->id ? 'bg-indigo-50/50' : '' }}">
                        <div class="flex-shrink-0 w-11 h-11 rounded-lg bg-brand-50 flex items-center justify-center overflow-hidden">
                            @if ($benefit->image)
                                <img src="{{ asset('storage/'.$benefit->image) }}" alt="" class="w-full h-full object-cover">
                            @elseif ($benefit->icon)
                                <x-icon :name="$benefit->icon" class="w-6 h-6 text-brand-600" />
                            @else
                                <x-icon name="sparkles" class="w-6 h-6 text-slate-300" />
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="font-medium text-slate-800">{{ $benefit->title }}</p>
                                <span class="text-xs text-slate-400">#{{ $benefit->display_order }}</span>
                                <span class="px-2 py-0.5 rounded text-xs {{ $benefit->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">{{ $benefit->status }}</span>
                            </div>
                            @if ($benefit->description)
                                <p class="text-sm text-slate-500 mt-0.5">{{ $benefit->description }}</p>
                            @endif
                        </div>
                        <div class="flex-shrink-0 space-x-3 text-sm">
                            <a href="{{ route('admin.products.benefits.index', [$product, 'edit' => $benefit->id]) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.products.benefits.destroy', [$product, $benefit]) }}" method="POST" class="inline" data-confirm="Delete this benefit?" data-confirm-danger>
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-10 text-center text-slate-400 text-sm">No benefits yet. Add the first one on the left.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-layouts.app>
