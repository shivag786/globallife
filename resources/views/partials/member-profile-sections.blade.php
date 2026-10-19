{{--
    Personal details, Photo & KYC, Address and Nominee sections, shared by the
    Branch Manager and Commission Partner forms. Every field is optional.

    @param \App\Models\User|null $member  the account being edited, null on create
--}}
@use('App\Models\UserProfile')
@php
    $profile = $member?->profile;
    $val = fn (string $field) => old($field, $profile?->{$field});
    $date = fn (string $field) => old($field, $profile?->{$field}?->format('Y-m-d'));
@endphp

{{-- ------------------------------------------------------------ Personal --}}
<x-form-section id="section-personal" title="Personal details" icon="identification"
                summary="Title, father / spouse name, gender, date of birth and blood group.">
    <div class="row g-4">
        <div class="col-6 col-md-2">
            <x-forms.input name="salutation" label="Title" as="select">
                <option value="">&mdash;</option>
                @foreach (UserProfile::SALUTATIONS as $salutation)
                    <option value="{{ $salutation }}" @selected($val('salutation') === $salutation)>{{ $salutation }}</option>
                @endforeach
            </x-forms.input>
        </div>

        <div class="col-12 col-md-10">
            <label for="relative_name" class="form-label small fw-medium">Father / Spouse name</label>
            <div class="input-group">
                <select name="relation_type" class="form-select flex-grow-0 w-auto" aria-label="Relationship to the person named">
                    <option value="">&mdash;</option>
                    @foreach (UserProfile::RELATION_TYPES as $value => $text)
                        <option value="{{ $value }}" @selected($val('relation_type') === $value)>{{ $text }}</option>
                    @endforeach
                </select>
                <input type="text" id="relative_name" name="relative_name" value="{{ $val('relative_name') }}"
                       class="form-control @error('relative_name') is-invalid @enderror" placeholder="e.g. Ram Kumar Sharma">
            </div>
            @error('relative_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="col-12 col-md-4">
            <x-forms.input name="gender" label="Gender" as="select">
                <option value="">&mdash; Not recorded &mdash;</option>
                @foreach (UserProfile::GENDERS as $value => $text)
                    <option value="{{ $value }}" @selected($val('gender') === $value)>{{ $text }}</option>
                @endforeach
            </x-forms.input>
        </div>

        <div class="col-8 col-md-4">
            <x-forms.input name="date_of_birth" type="date" label="Date of birth" :value="$date('date_of_birth')"
                           :max="now()->subYears(18)->toDateString()" data-dob />
        </div>

        <div class="col-4 col-md-2">
            <label class="form-label small fw-medium">Age</label>
            <div class="form-control bg-body-tertiary" data-age-out aria-live="polite">
                {{ $profile?->age() ?? '—' }}
            </div>
        </div>

        <div class="col-12 col-md-2">
            <x-forms.input name="blood_group" label="Blood group" as="select">
                <option value="">&mdash;</option>
                @foreach (UserProfile::BLOOD_GROUPS as $group)
                    <option value="{{ $group }}" @selected($val('blood_group') === $group)>{{ $group }}</option>
                @endforeach
            </x-forms.input>
        </div>
    </div>
</x-form-section>

{{-- ---------------------------------------------------------- Photo & KYC --}}
<x-form-section id="section-kyc" title="Photo & KYC" icon="lock" :open="$errors->hasAny(['photo', 'aadhaar_file', 'pan_file', 'aadhaar_number', 'pan_number']) || ! $member"
                summary="Passport photo, Aadhaar and PAN. Every field optional.">
    <div class="alert alert-light border fs-sm d-flex gap-2">
        <x-icon name="lock" class="w-4 h-4 flex-shrink-0 mt-1" />
        <div>
            Identity documents are stored privately and are only visible to the people who manage
            this account &mdash; never from a public link. Aadhaar and PAN numbers are encrypted and
            shown masked. Each upload is re-saved as a compressed image, which also removes the
            location data a phone photo carries.
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-4">
            <x-forms.kyc-upload kind="photo" label="Passport photo" :owner="$member" />
        </div>

        <div class="col-12 col-lg-8">
            <div class="row g-4">
                <div class="col-12 col-md-6">
                    <x-forms.input name="aadhaar_number" label="Aadhaar number" class="font-monospace"
                                   inputmode="numeric" autocomplete="off" placeholder="1234 5678 9012" maxlength="14"
                                   data-aadhaar :value="''"
                                   :help="$profile?->maskedAadhaar()
                                       ? 'On record: '.$profile->maskedAadhaar().'. Leave blank to keep it.'
                                       : '12 digits. Spaces are added as you type.'" />
                    @if ($profile?->maskedAadhaar())
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" name="clear_aadhaar_number" value="1" id="clear_aadhaar_number">
                            <label class="form-check-label fs-xs" for="clear_aadhaar_number">Remove the Aadhaar number on record</label>
                        </div>
                    @endif
                </div>

                <div class="col-12 col-md-6">
                    <x-forms.input name="pan_number" label="PAN" class="font-monospace text-uppercase"
                                   autocomplete="off" placeholder="ABCDE1234F" maxlength="10" data-pan :value="''"
                                   :help="$profile?->maskedPan()
                                       ? 'On record: '.$profile->maskedPan().'. Leave blank to keep it.'
                                       : 'Five letters, four digits, one letter.'" />
                    @if ($profile?->maskedPan())
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" name="clear_pan_number" value="1" id="clear_pan_number">
                            <label class="form-check-label fs-xs" for="clear_pan_number">Remove the PAN on record</label>
                        </div>
                    @endif
                </div>

                <div class="col-12 col-sm-6">
                    <x-forms.kyc-upload kind="aadhaar_file" label="Aadhaar card scan" :owner="$member" />
                </div>

                <div class="col-12 col-sm-6">
                    <x-forms.kyc-upload kind="pan_file" label="PAN card scan" :owner="$member" />
                </div>
            </div>
        </div>
    </div>
</x-form-section>

{{-- -------------------------------------------------------------- Address --}}
<x-form-section id="section-address" title="Address" icon="home" summary="Where this person lives.">
    <div class="row g-4">
        <div class="col-12">
            <x-forms.input name="address_line1" label="Address line 1" :value="$val('address_line1')"
                           placeholder="House / flat no., building, street" autocomplete="address-line1" />
        </div>
        <div class="col-12">
            <x-forms.input name="address_line2" label="Address line 2" :value="$val('address_line2')"
                           placeholder="Area, landmark (optional)" autocomplete="address-line2" />
        </div>
        <div class="col-md-5">
            <x-forms.input name="city" label="City" :value="$val('city')" autocomplete="address-level2" />
        </div>
        <div class="col-md-4">
            <x-forms.input name="state" label="State" :value="$val('state')" autocomplete="address-level1" />
        </div>
        <div class="col-md-3">
            <x-forms.input name="pincode" label="PIN code" :value="$val('pincode')" inputmode="numeric"
                           maxlength="6" placeholder="6 digits" autocomplete="postal-code" />
        </div>
    </div>
</x-form-section>

{{-- -------------------------------------------------------------- Nominee --}}
<x-form-section id="section-nominee" title="Nominee" icon="users" summary="Who receives payouts if this person cannot.">
    <div class="row g-4">
        <div class="col-md-6">
            <x-forms.input name="nominee_name" label="Nominee name" :value="$val('nominee_name')" />
        </div>
        <div class="col-md-6">
            <x-forms.input name="nominee_relation" label="Relationship" as="select">
                <option value="">&mdash;</option>
                @foreach (UserProfile::NOMINEE_RELATIONS as $value => $text)
                    <option value="{{ $value }}" @selected($val('nominee_relation') === $value)>{{ $text }}</option>
                @endforeach
            </x-forms.input>
        </div>
        <div class="col-md-6">
            <x-forms.input name="nominee_date_of_birth" type="date" label="Nominee date of birth"
                           :value="$date('nominee_date_of_birth')" :max="now()->toDateString()" />
        </div>
        <div class="col-md-6">
            <x-forms.mobile name="nominee_mobile" label="Nominee mobile" :value="$val('nominee_mobile')" />
        </div>
    </div>
</x-form-section>
