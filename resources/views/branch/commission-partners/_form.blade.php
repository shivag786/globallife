@csrf
@isset($partner) @method('PUT') @endisset

@php
    $assignedCityIds = isset($partner) ? $partner->cities->pluck('id')->all() : old('cities', []);
@endphp

<div class="bootstrap-scope max-w-3xl">
    <div class="d-flex flex-column gap-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h2 class="h6 mb-0 fw-semibold">Commission Partner Details</h2>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <x-forms.input name="name" label="Name" :value="$partner->name ?? ''" required />
                    </div>
                    <div class="col-md-6">
                        <x-forms.input name="email" type="email" label="Email" :value="$partner->email ?? ''" required />
                    </div>
                    @unless (isset($partner))
                        <div class="col-md-6">
                            <x-forms.input name="password" type="password" label="Password" required />
                        </div>
                    @endunless
                    <div class="col-md-6">
                        <x-forms.input name="commission_percentage" type="number" label="Commission Percentage"
                                       :value="$partner->commission_percentage ?? ''"
                                       step="0.01" min="0" max="{{ auth()->user()->commission_percentage }}" required
                                       help="Up to your own cap of {{ auth()->user()->commission_percentage }}%." />
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h2 class="h6 mb-0 fw-semibold">Cities</h2>
                <p class="small text-muted mb-0">Only cities assigned to your branch are selectable.</p>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @forelse ($cities as $city)
                        <div class="col-md-4">
                            <label class="d-flex align-items-center gap-2">
                                <input type="checkbox" name="cities[]" value="{{ $city->id }}"
                                       @checked(in_array($city->id, $assignedCityIds))>
                                <span class="text-sm">{{ $city->name }}, {{ $city->state }}</span>
                            </label>
                        </div>
                    @empty
                        <p class="text-slate-400 text-sm">You have no cities assigned yet. Contact your Super Admin.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div>
            <button type="submit" class="bg-brand-700 text-white text-sm px-[1.25rem] py-2.5 rounded-md hover:bg-brand-800 transition font-medium">
                {{ isset($partner) ? 'Update Commission Partner' : 'Create Commission Partner' }}
            </button>
        </div>
    </div>
</div>
