<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Personal, address, nominee and KYC details for a Branch Manager or a
 * Commission Partner.
 *
 * Aadhaar and PAN are encrypted at rest and only ever displayed masked; the
 * photo and document scans sit on the private `kyc` disk and are served by
 * KycFileController to the people allowed to see them.
 */
#[Fillable([
    'user_id',
    'salutation', 'relation_type', 'relative_name', 'gender', 'date_of_birth', 'blood_group',
    'alternate_mobile',
    'address_line1', 'address_line2', 'city', 'state', 'pincode',
    'nominee_name', 'nominee_relation', 'nominee_date_of_birth', 'nominee_mobile',
    'aadhaar_number', 'pan_number', 'photo_path', 'aadhaar_file_path', 'pan_file_path',
])]
#[Hidden(['aadhaar_number', 'pan_number'])]
class UserProfile extends Model
{
    public const SALUTATIONS = ['Mr.', 'Mrs.', 'Ms.', 'Dr.'];

    public const RELATION_TYPES = [
        's/o' => 'Son of (S/O)',
        'd/o' => 'Daughter of (D/O)',
        'w/o' => 'Wife of (W/O)',
        'h/o' => 'Husband of (H/O)',
        'c/o' => 'Care of (C/O)',
    ];

    public const GENDERS = ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'];

    public const BLOOD_GROUPS = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

    public const NOMINEE_RELATIONS = [
        'spouse' => 'Spouse', 'son' => 'Son', 'daughter' => 'Daughter', 'father' => 'Father',
        'mother' => 'Mother', 'brother' => 'Brother', 'sister' => 'Sister', 'other' => 'Other',
    ];

    /** Uploadable files and the column each is stored in. */
    public const FILES = [
        'photo' => 'photo_path',
        'aadhaar_file' => 'aadhaar_file_path',
        'pan_file' => 'pan_file_path',
    ];

    public const FILE_LABELS = [
        'photo' => 'Passport photo',
        'aadhaar_file' => 'Aadhaar card',
        'pan_file' => 'PAN card',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'nominee_date_of_birth' => 'date',
            'aadhaar_number' => 'encrypted',
            'pan_number' => 'encrypted',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function age(): ?int
    {
        return $this->date_of_birth?->age;
    }

    /** "XXXX XXXX 9012" -- never the full number on screen. */
    public function maskedAadhaar(): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $this->aadhaar_number);

        return strlen($digits) === 12 ? 'XXXX XXXX '.substr($digits, -4) : null;
    }

    /** "XXXXX1234X" -- only the middle digits shown, the usual PAN masking. */
    public function maskedPan(): ?string
    {
        $pan = (string) $this->pan_number;

        return strlen($pan) === 10 ? 'XXXXX'.substr($pan, 5, 4).'X' : null;
    }

    public function filePath(string $kind): ?string
    {
        $column = self::FILES[$kind] ?? null;

        return $column ? $this->{$column} : null;
    }

    public function hasFile(string $kind): bool
    {
        return (bool) $this->filePath($kind);
    }

    /** "S/O Ram Kumar Sharma", or null. */
    public function relationLine(): ?string
    {
        if (! $this->relative_name) {
            return null;
        }

        return trim(strtoupper((string) $this->relation_type).' '.$this->relative_name);
    }
}
