@csrf
@isset($manager) @method('PUT') @endisset

@php
    $member = $manager ?? null;
    $assignedCityIds = old('cities', $member ? $member->branchCities->pluck('id')->all() : []);
    $profile = $member?->profile;
@endphp

<x-member-form :sections="[
    ['id' => 'section-account', 'title' => 'Account', 'icon' => 'user'],
    ['id' => 'section-contact', 'title' => 'Contact', 'icon' => 'phone'],
    ['id' => 'section-role', 'title' => 'Commission & branches', 'icon' => 'briefcase'],
    ['id' => 'section-personal', 'title' => 'Personal details', 'icon' => 'identification'],
    ['id' => 'section-kyc', 'title' => 'Photo & KYC', 'icon' => 'lock'],
    ['id' => 'section-address', 'title' => 'Address', 'icon' => 'home'],
    ['id' => 'section-nominee', 'title' => 'Nominee', 'icon' => 'users'],
]">

    {{-- ---------------------------------------------------------- Account --}}
    <x-form-section id="section-account" title="Account" icon="user"
                    summary="Their name and sign-in details{{ ($seesAll ?? false) ? ', and which Sub Admin manages them' : '' }}.">
        <div class="row g-4">
            <div class="col-md-6">
                <x-forms.input name="name" label="Full name" :value="$member->name ?? ''" required autocomplete="name" />
            </div>
            <div class="col-md-6">
                <x-forms.input name="email" type="email" label="Email" :value="$member->email ?? ''" required autocomplete="email" />
            </div>
            @unless ($member)
                <div class="col-md-6">
                    <x-forms.input name="password" type="password" label="Password" required autocomplete="new-password"
                                   help="At least 8 characters. They can be given a new one later from this edit screen." />
                </div>
            @endunless

            {{-- Which Sub Admin runs this Branch Manager. Super Admin only: a Sub
                 Admin's own Branch Managers are always theirs, so they are never
                 shown a field that could move one away. --}}
            @if ($seesAll ?? false)
                <div class="col-md-6">
                    <x-forms.input name="created_by" label="Managed By" as="select"
                                   help="The Sub Admin who manages them. Leave as Super Admin to keep them yourself.">
                        <option value="">Super Admin (unassigned)</option>
                        @foreach ($subAdmins as $subAdmin)
                            <option value="{{ $subAdmin->id }}"
                                @selected((int) old('created_by', $member->created_by ?? null) === $subAdmin->id)>
                                {{ $subAdmin->name }}
                            </option>
                        @endforeach
                    </x-forms.input>
                </div>
            @endif
        </div>
    </x-form-section>

    {{-- ---------------------------------------------------------- Contact --}}
    <x-form-section id="section-contact" title="Contact" icon="phone" summary="How the office reaches them.">
        <div class="row g-4">
            <div class="col-md-6">
                <x-forms.mobile name="mobile" label="Mobile" :value="$member->mobile ?? ''"
                                help="A +91 or leading 0 is trimmed automatically." />
            </div>
            <div class="col-md-6">
                <x-forms.mobile name="alternate_mobile" label="Alternate mobile"
                                :value="old('alternate_mobile', $profile?->alternate_mobile)" />
            </div>
        </div>
    </x-form-section>

    {{-- -------------------------------------------- Commission & branches --}}
    <x-form-section id="section-role" title="Commission & branches" icon="briefcase"
                    summary="Their commission cap, and the cities they cover.">
        <div class="row g-4">
            <div class="col-md-6">
                <x-forms.input name="commission_percentage" type="number" label="Commission percentage cap"
                               :value="$member->commission_percentage ?? ''" step="0.01" min="0" max="100" required
                               help="The most this Branch Manager can grant a Commission Partner (0–100%)." />
            </div>

            <div class="col-12">
                <label class="form-label small fw-medium">
                    Assigned branches (cities) <span class="text-secondary fw-normal">(optional)</span>
                </label>
                <p class="form-text mt-0 mb-2">
                    Leave empty if their territory isn't decided yet. They also pick up a city automatically
                    whenever one of their partners registers a member there.
                </p>
                <div class="row g-2">
                    @forelse ($cities as $city)
                        <div class="col-sm-6 col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="cities[]" value="{{ $city->id }}"
                                       id="city-{{ $city->id }}" @checked(in_array($city->id, $assignedCityIds))>
                                <label class="form-check-label fs-sm" for="city-{{ $city->id }}">
                                    {{ $city->name }}, {{ $city->state }}
                                </label>
                            </div>
                        </div>
                    @empty
                        <p class="text-secondary fs-sm mb-0">No cities yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </x-form-section>

    @include('partials.member-profile-sections', ['member' => $member])

    <div class="member-form-actions">
        <button type="submit" class="btn btn-brand">
            {{ $member ? 'Update Branch Manager' : 'Create Branch Manager' }}
        </button>
        <a href="{{ route('admin.branch-managers.index') }}" class="btn btn-link text-secondary">Cancel</a>
    </div>
</x-member-form>
