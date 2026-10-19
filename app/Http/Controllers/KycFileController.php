<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Services\MemberProfileService;
use App\Support\KycAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves a member's passport photo or document scan from the private kyc disk.
 *
 * These files never sit under a public URL; this is the only way to reach them,
 * and only for the people KycAccess allows. Responses are not cached anywhere
 * and are sandboxed, since the files are user-supplied.
 */
class KycFileController extends Controller
{
    public function __invoke(User $user, string $kind): StreamedResponse
    {
        abort_unless(array_key_exists($kind, UserProfile::FILES), 404);

        // 404, not 403, so the route cannot be used to learn who has a profile.
        abort_unless(KycAccess::canView(Auth::user(), $user), 404);

        $path = $user->profile?->filePath($kind);
        $disk = Storage::disk(MemberProfileService::DISK);

        abort_unless($path && $disk->exists($path), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
