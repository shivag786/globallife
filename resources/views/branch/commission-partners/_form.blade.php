@csrf
@isset($partner) @method('PUT') @endisset

@php
    $member = $partner ?? null;
    $profile = $member?->profile;
    $cap = auth()->user()->commission_percentage;
@endphp

<x-member-form :sections="[
    ['id' => 'section-account', 'title' => 'Account', 'icon' => 'user'],
    ['id' => 'section-contact', 'title' => 'Contact', 'icon' => 'phone'],
    ['id' => 'section-role', 'title' => 'Commission', 'icon' => 'rupee'],
    ['id' => 'section-personal', 'title' => 'Personal details', 'icon' => 'identification'],
    ['id' => 'section-kyc', 'title' => 'Photo & KYC', 'icon' => 'lock'],
    ['id' => 'section-address', 'title' => 'Address', 'icon' => 'home'],
    ['id' => 'section-nominee', 'title' => 'Nominee', 'icon' => 'users'],
]">

    {{-- ---------------------------------------------------------- Account --}}
    <x-form-section id="section-account" title="Account" icon="user" summary="Their name and sign-in details.">
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
                                   help="At least 8 characters. You can set a new one later from this edit screen." />
                </div>
            @endunless
        </div>
    </x-form-section>

    {{-- ---------------------------------------------------------- Contact --}}
    <x-form-section id="section-contact" title="Contact" icon="phone" summary="How you and the office reach them.">
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

    {{-- ------------------------------------------------------- Commission --}}
    <x-form-section id="section-role" title="Commission" icon="rupee" summary="Their share of each VIP activation.">
        <div class="row g-4">
            <div class="col-md-6">
                <x-forms.input name="commission_percentage" type="number" label="Commission percentage"
                               :value="$member->commission_percentage ?? ''" step="0.01" min="0" :max="$cap" required
                               :help="'Up to your own cap of '.$cap.'%.'" />
            </div>
        </div>
    </x-form-section>

    @include('partials.member-profile-sections', ['member' => $member])

    <div class="member-form-actions">
        <button type="submit" class="btn btn-brand">
            {{ $member ? 'Update Commission Partner' : 'Create Commission Partner' }}
        </button>
        <a href="{{ route('branch.commission-partners.index') }}" class="btn btn-link text-secondary">Cancel</a>
    </div>
</x-member-form>
