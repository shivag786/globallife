<?php

namespace App\Http\Requests\Concerns;

use App\Models\UserProfile;
use App\Support\MobileNumber;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

/**
 * The personal / address / nominee / KYC fields shared by the Branch Manager
 * and Commission Partner forms. Every one of them is optional.
 */
trait ValidatesMemberProfile
{
    /**
     * @return array<string, mixed>
     */
    protected function profileRules(): array
    {
        $image = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'];

        return [
            'salutation' => ['nullable', Rule::in(UserProfile::SALUTATIONS)],
            'relation_type' => ['nullable', Rule::in(array_keys(UserProfile::RELATION_TYPES))],
            'relative_name' => ['nullable', 'string', 'max:150'],
            'gender' => ['nullable', Rule::in(array_keys(UserProfile::GENDERS))],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString(), 'after:1900-01-01'],
            'blood_group' => ['nullable', Rule::in(UserProfile::BLOOD_GROUPS)],

            'alternate_mobile' => ['nullable', 'digits:'.MobileNumber::LENGTH, 'different:mobile'],

            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'regex:/^[1-9][0-9]{5}$/'],

            'nominee_name' => ['nullable', 'string', 'max:150'],
            'nominee_relation' => ['nullable', Rule::in(array_keys(UserProfile::NOMINEE_RELATIONS))],
            'nominee_date_of_birth' => ['nullable', 'date', 'before_or_equal:today', 'after:1900-01-01'],
            'nominee_mobile' => ['nullable', 'digits:'.MobileNumber::LENGTH],

            'aadhaar_number' => ['nullable', 'digits:12'],
            'pan_number' => ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'],
            'clear_aadhaar_number' => ['nullable', 'boolean'],
            'clear_pan_number' => ['nullable', 'boolean'],

            'photo' => $image,
            'aadhaar_file' => $image,
            'pan_file' => $image,
            'remove_files' => ['nullable', 'array'],
            'remove_files.*' => [Rule::in(array_keys(UserProfile::FILES))],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function profileMessages(): array
    {
        return [
            'date_of_birth.before_or_equal' => 'They must be at least 18 years old.',
            'alternate_mobile.different' => 'The alternate mobile must be a different number.',
            'pincode.regex' => 'The PIN code must be 6 digits and cannot start with 0.',
            'aadhaar_number.digits' => 'The Aadhaar number must be 12 digits.',
            'pan_number.regex' => 'The PAN must be five letters, four digits and one letter, e.g. ABCDE1234F.',
            'photo.max' => 'The photo must be under 8 MB.',
            'aadhaar_file.max' => 'The Aadhaar scan must be under 8 MB.',
            'pan_file.max' => 'The PAN scan must be under 8 MB.',
        ];
    }

    /**
     * Normalise before any rule runs: the mobiles to 10 digits, Aadhaar to bare
     * digits, PAN to upper case.
     */
    protected function prepareProfileForValidation(): void
    {
        $merge = [];

        foreach (['alternate_mobile', 'nominee_mobile'] as $field) {
            if ($this->filled($field)) {
                $merge[$field] = MobileNumber::normalise($this->input($field));
            }
        }

        if ($this->filled('aadhaar_number')) {
            $merge['aadhaar_number'] = preg_replace('/\D/', '', (string) $this->input('aadhaar_number'));
        }

        if ($this->filled('pan_number')) {
            $merge['pan_number'] = strtoupper(preg_replace('/\s+/', '', (string) $this->input('pan_number')));
        }

        if ($merge) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function profileData(): array
    {
        return collect($this->validated())
            ->only(array_keys($this->profileRules()))
            ->except(['photo', 'aadhaar_file', 'pan_file', 'remove_files'])
            ->all();
    }

    /**
     * @return array<string, UploadedFile>
     */
    public function profileFiles(): array
    {
        return collect(array_keys(UserProfile::FILES))
            ->mapWithKeys(fn ($kind) => [$kind => $this->file($kind)])
            ->filter(fn ($file) => $file instanceof UploadedFile)
            ->all();
    }

    /**
     * @return list<string>
     */
    public function profileRemovals(): array
    {
        return array_values((array) $this->validated('remove_files', []));
    }
}
