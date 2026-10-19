<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserProfile;
use App\Support\KycImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Saves the personal / address / nominee / KYC details for a Branch Manager or
 * a Commission Partner.
 *
 * Text fields are written as given (the form request has already normalised
 * them). Aadhaar and PAN are only replaced when a new value is typed: the edit
 * form never shows the stored numbers, so a blank field means "keep". Images
 * are re-encoded by KycImage and written to the private `kyc` disk.
 */
class MemberProfileService
{
    public const DISK = 'kyc';

    private const TEXT_FIELDS = [
        'salutation', 'relation_type', 'relative_name', 'gender', 'date_of_birth', 'blood_group',
        'alternate_mobile',
        'address_line1', 'address_line2', 'city', 'state', 'pincode',
        'nominee_name', 'nominee_relation', 'nominee_date_of_birth', 'nominee_mobile',
    ];

    /**
     * @param  array<string, mixed>  $data  validated input
     * @param  array<string, UploadedFile>  $files  photo / aadhaar_file / pan_file
     * @param  list<string>  $remove  file kinds to delete
     */
    public function save(User $user, array $data, array $files = [], array $remove = []): UserProfile
    {
        $profile = $user->profile ?? new UserProfile(['user_id' => $user->id]);

        foreach (self::TEXT_FIELDS as $field) {
            if (array_key_exists($field, $data)) {
                $profile->{$field} = $data[$field] === '' ? null : $data[$field];
            }
        }

        foreach (['aadhaar_number', 'pan_number'] as $field) {
            if (! empty($data[$field])) {
                $profile->{$field} = $data[$field];
            }
        }

        if (! empty($data['clear_aadhaar_number'])) {
            $profile->aadhaar_number = null;
        }

        if (! empty($data['clear_pan_number'])) {
            $profile->pan_number = null;
        }

        $disk = Storage::disk(self::DISK);
        $replaced = [];

        foreach (UserProfile::FILES as $kind => $column) {
            $file = $files[$kind] ?? null;

            if ($file instanceof UploadedFile) {
                $image = $kind === 'photo' ? KycImage::passportPhoto($file) : KycImage::document($file);
                $path = "members/{$user->id}/{$kind}-".Str::random(16).'.'.$image['extension'];

                $disk->put($path, $image['bytes']);

                if ($profile->{$column}) {
                    $replaced[] = $profile->{$column};
                }

                $profile->{$column} = $path;
            } elseif (in_array($kind, $remove, true) && $profile->{$column}) {
                $replaced[] = $profile->{$column};
                $profile->{$column} = null;
            }
        }

        $profile->save();

        // Only after the row points at the new files, so a failed save never
        // leaves a profile pointing at a file that has been deleted.
        foreach ($replaced as $old) {
            $disk->delete($old);
        }

        return $profile;
    }
}
