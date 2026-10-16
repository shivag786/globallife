<x-layouts.public title="My Addresses">
    @php $field = 'form-control'; @endphp

    <div class="mw-3xl mx-auto px-6 py-16">
        <div class="d-flex align-items-center justify-content-between mb-8">
            <h1 class="font-display fs-3xl fw-bold text-brand-emphasis">My Addresses</h1>
            <a href="{{ route('account.orders.index') }}" class="fs-sm text-brand hover-underline">My Orders →</a>
        </div>

        @if ($errors->any())
            <div class="mb-6 fs-sm text-danger bg-danger-subtle border border-danger-subtle rounded-2 p-4">
                <ul class="list-disc list-inside d-flex flex-column gap-1">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- Saved addresses --}}
        <div class="d-flex flex-column gap-3 mb-8">
            @forelse ($addresses as $address)
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-4 bg-body border rounded-4 p-5">
                    <div class="fs-sm">
                        <p class="fw-medium text-brand-emphasis">
                            {{ $address->name }} · {{ $address->phone }}
                            @if ($address->is_default)<span class="ms-1 fs-xs text-brand bg-brand-subtle px-2 py-1 rounded-pill">Default</span>@endif
                        </p>
                        <p class="text-secondary mt-1">{{ $address->summary() }}</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        @unless ($address->is_default)
                            <form method="POST" action="{{ route('account.addresses.default', $address) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-link p-0 text-decoration-none fs-xs text-brand hover-underline">Set default</button>
                            </form>
                        @endunless
                        <form method="POST" action="{{ route('account.addresses.destroy', $address) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-link p-0 text-decoration-none fs-xs text-danger hover-underline">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-secondary fs-sm">You have no saved addresses yet.</p>
            @endforelse
        </div>

        {{-- Add address --}}
        <div class="bg-body border rounded-4 p-6">
            <h2 class="fw-semibold text-brand-emphasis mb-4">Add a new address</h2>
            <form method="POST" action="{{ route('account.addresses.store') }}">
                @csrf
                <div class="d-grid grid-cols-sm-2 gap-4">
                    <div>
                        <label class="d-block fs-sm fw-medium text-body-secondary">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user?->name) }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label class="d-block fs-sm fw-medium text-body-secondary">Mobile</label>
                        <input type="text" name="phone" value="{{ old('phone', $user?->mobile) }}" required class="{{ $field }}">
                    </div>
                    <div class="col-span-sm-2">
                        <label class="d-block fs-sm fw-medium text-body-secondary">Address</label>
                        <textarea name="address" rows="2" required class="{{ $field }}">{{ old('address') }}</textarea>
                    </div>
                    <div>
                        <label class="d-block fs-sm fw-medium text-body-secondary">City</label>
                        <input type="text" name="city" value="{{ old('city') }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label class="d-block fs-sm fw-medium text-body-secondary">State</label>
                        <input type="text" name="state" value="{{ old('state') }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label class="d-block fs-sm fw-medium text-body-secondary">PIN Code</label>
                        <input type="text" name="pincode" value="{{ old('pincode') }}" required class="{{ $field }}">
                    </div>
                    <label class="d-flex align-items-center gap-2 fs-sm text-body-secondary">
                        <input type="checkbox" name="is_default" value="1" class="rounded text-brand"> Make this my default
                    </label>
                </div>
                <button type="submit" class="btn btn-brand btn-sm rounded-pill mt-5">Save address</button>
            </form>
        </div>
    </div>
</x-layouts.public>
