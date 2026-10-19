@csrf
@isset($subAdmin) @method('PUT') @endisset

@php
    $member = $subAdmin ?? null;
    $profile = $member?->profile;
@endphp

<x-member-form :sections="[
    ['id' => 'section-account', 'title' => 'Account', 'icon' => 'user'],
    ['id' => 'section-contact', 'title' => 'Contact', 'icon' => 'phone'],
    ['id' => 'section-personal', 'title' => 'Personal details', 'icon' => 'identification'],
    ['id' => 'section-kyc', 'title' => 'Photo & KYC', 'icon' => 'lock'],
    ['id' => 'section-address', 'title' => 'Address', 'icon' => 'home'],
    ['id' => 'section-nominee', 'title' => 'Nominee', 'icon' => 'users'],
]">

    {{-- ---------------------------------------------------------- Account --}}
    <x-form-section id="section-account" title="Account" icon="user"
                    summary="Their name and sign-in details. What they can reach is set on the Permissions screen.">
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

    @include('partials.member-profile-sections', ['member' => $member])

    <div class="member-form-actions">
        <button type="submit" class="btn btn-brand">
            {{ $member ? 'Update Sub Admin' : 'Create Sub Admin' }}
        </button>
        <a href="{{ route('admin.sub-admins.index') }}" class="btn btn-link text-secondary">Cancel</a>
    </div>
</x-member-form>
