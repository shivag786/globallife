@csrf
@isset($manager) @method('PUT') @endisset

@php
    $assignedCityIds = isset($manager) ? $manager->branchCities->pluck('id')->all() : old('cities', []);
@endphp

<div class="bootstrap-scope max-w-3xl">
    <div class="d-flex flex-column gap-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h2 class="h6 mb-0 fw-semibold">Branch Manager Details</h2>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <x-forms.input name="name" label="Name" :value="$manager->name ?? ''" required />
                    </div>
                    <div class="col-md-6">
                        <x-forms.input name="mobile" label="Mobile Number" :value="$manager->mobile ?? ''"
                                       data-mobile-input type="tel" inputmode="numeric" maxlength="10"
                                       autocomplete="tel-national" placeholder="9876543210"
                                       help="10 digits. A +91 or leading 0 is trimmed automatically." />
                    </div>
                    <div class="col-md-6">
                        <x-forms.input name="email" type="email" label="Email" :value="$manager->email ?? ''" required />
                    </div>
                    @unless (isset($manager))
                        <div class="col-md-6">
                            <x-forms.input name="password" type="password" label="Password" required />
                        </div>
                    @endunless
                    <div class="col-md-6">
                        <x-forms.input name="commission_percentage" type="number" label="Commission Percentage Cap"
                                       :value="$manager->commission_percentage ?? ''"
                                       step="0.01" min="0" max="100" required
                                       help="The most this Branch Manager can grant a Commission Partner (0–100%)." />
                    </div>
                    {{-- Which Sub Admin runs this Branch Manager. Super Admin only:
                         a Sub Admin's own Branch Managers are always theirs, so they
                         are never shown a field that could move one away. --}}
                    @if ($seesAll ?? false)
                        <div class="col-md-6">
                            <label for="created_by" class="block text-sm font-medium text-slate-700">Managed By</label>
                            <select id="created_by" name="created_by"
                                    class="form-select @error('created_by') is-invalid @enderror">
                                <option value="">Super Admin (unassigned)</option>
                                @foreach ($subAdmins as $subAdmin)
                                    <option value="{{ $subAdmin->id }}"
                                        @selected((int) old('created_by', $manager->created_by ?? null) === $subAdmin->id)>
                                        {{ $subAdmin->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('created_by')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                            <p class="small text-muted mb-0 mt-1">
                                The Sub Admin who manages them. Leave as Super Admin to keep them yourself
                                &mdash; a Sub Admin only ever sees their own.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h2 class="h6 mb-0 fw-semibold">Assigned Branches (Cities) <span class="fw-normal text-muted small">— optional</span></h2>
                <p class="small text-muted mb-0">
                    Leave empty if their territory isn't decided yet. They also pick up a city
                    automatically whenever they assign a Commission Partner to one.
                </p>
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
                        <p class="text-slate-400 text-sm">Create a city first.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div>
            <button type="submit" class="bg-brand-700 text-white text-sm px-[1.25rem] py-2.5 rounded-md hover:bg-brand-800 transition font-medium">
                {{ isset($manager) ? 'Update Branch Manager' : 'Create Branch Manager' }}
            </button>
        </div>
    </div>
</div>
