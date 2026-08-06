<x-layouts.public title="My Addresses">
    @php $field = 'mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500'; @endphp

    <div class="max-w-3xl mx-auto px-6 py-16">
        <div class="flex items-center justify-between mb-8">
            <h1 class="font-display text-3xl font-bold text-brand-900">My Addresses</h1>
            <a href="{{ route('account.orders.index') }}" class="text-sm text-brand-700 hover:underline">My Orders →</a>
        </div>

        @if ($errors->any())
            <div class="mb-6 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg p-4">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- Saved addresses --}}
        <div class="space-y-3 mb-8">
            @forelse ($addresses as $address)
                <div class="flex flex-wrap items-start justify-between gap-4 bg-white border border-slate-100 rounded-2xl p-5">
                    <div class="text-sm">
                        <p class="font-medium text-brand-900">
                            {{ $address->name }} · {{ $address->phone }}
                            @if ($address->is_default)<span class="ml-1 text-xs text-brand-700 bg-brand-100 px-1.5 py-0.5 rounded-full">Default</span>@endif
                        </p>
                        <p class="text-slate-500 mt-0.5">{{ $address->summary() }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @unless ($address->is_default)
                            <form method="POST" action="{{ route('account.addresses.default', $address) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-xs text-brand-700 hover:underline">Set default</button>
                            </form>
                        @endunless
                        <form method="POST" action="{{ route('account.addresses.destroy', $address) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-slate-500 text-sm">You have no saved addresses yet.</p>
            @endforelse
        </div>

        {{-- Add address --}}
        <div class="bg-white border border-slate-100 rounded-2xl p-6">
            <h2 class="font-semibold text-brand-900 mb-4">Add a new address</h2>
            <form method="POST" action="{{ route('account.addresses.store') }}">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user?->name) }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Mobile</label>
                        <input type="text" name="phone" value="{{ old('phone', $user?->mobile) }}" required class="{{ $field }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Address</label>
                        <textarea name="address" rows="2" required class="{{ $field }}">{{ old('address') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">City</label>
                        <input type="text" name="city" value="{{ old('city') }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">State</label>
                        <input type="text" name="state" value="{{ old('state') }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">PIN Code</label>
                        <input type="text" name="pincode" value="{{ old('pincode') }}" required class="{{ $field }}">
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="is_default" value="1" class="rounded text-brand-600 focus:ring-brand-500"> Make this my default
                    </label>
                </div>
                <button type="submit" class="mt-5 bg-brand-700 text-white text-sm px-5 py-2.5 rounded-full font-medium hover:bg-brand-800 transition">Save address</button>
            </form>
        </div>
    </div>
</x-layouts.public>
