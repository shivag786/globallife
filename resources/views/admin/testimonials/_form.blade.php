@csrf
@isset($testimonial) @method('PUT') @endisset

<div class="space-y-4 max-w-xl">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name', $testimonial->name ?? '') }}" required
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="city" class="block text-sm font-medium text-slate-700">City</label>
            <input id="city" type="text" name="city" value="{{ old('city', $testimonial->city ?? '') }}"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="role_title" class="block text-sm font-medium text-slate-700">Role <span class="text-slate-400">(Customer, Distributor...)</span></label>
            <input id="role_title" type="text" name="role_title" value="{{ old('role_title', $testimonial->role_title ?? '') }}"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="rating" class="block text-sm font-medium text-slate-700">Rating</label>
            <select id="rating" name="rating" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @for ($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" @selected(old('rating', $testimonial->rating ?? 5) == $i)>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                @endfor
            </select>
        </div>
    </div>

    <div>
        <label for="content" class="block text-sm font-medium text-slate-700">Testimonial</label>
        <textarea id="content" name="content" rows="4" required
                  class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('content', $testimonial->content ?? '') }}</textarea>
    </div>

    <div>
        <label for="photo" class="block text-sm font-medium text-slate-700">Photo</label>
        @if (isset($testimonial) && $testimonial->photo)
            <img src="{{ asset('storage/'.$testimonial->photo) }}" alt="" class="h-16 w-16 object-cover rounded-full mt-1 mb-2">
        @endif
        <input id="photo" type="file" name="photo" accept="image/*" class="mt-1 block w-full text-sm text-slate-600">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach (['active', 'inactive'] as $option)
                    <option value="{{ $option }}" @selected(old('status', $testimonial->status ?? 'active') === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="display_order" class="block text-sm font-medium text-slate-700">Display Order</label>
            <input id="display_order" type="number" min="0" name="display_order" value="{{ old('display_order', $testimonial->display_order ?? 0) }}"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">
        {{ isset($testimonial) ? 'Update Testimonial' : 'Create Testimonial' }}
    </button>
</div>
