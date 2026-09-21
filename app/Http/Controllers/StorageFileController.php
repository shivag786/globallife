<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves files from the `public` disk at /storage/{path}.
 *
 * Normally Apache answers that URL straight off the `public/storage` symlink and
 * this never runs. It exists because the symlink cannot be relied on: it lives
 * inside the deploy directory, is gitignored, and Hostinger's auto-deploy does a
 * clean checkout — so a deploy can silently delete it and take every image on
 * the site down. Laravel only reaches this route when the file was NOT found on
 * disk by the web server, so it costs nothing while the symlink is healthy.
 */
class StorageFileController extends Controller
{
    public function __invoke(string $path): StreamedResponse
    {
        $disk = Storage::disk('public');

        // Reject traversal before touching the filesystem.
        abort_if($path === '' || str_contains($path, '..') || str_contains($path, "\0"), 404);
        abort_unless($disk->exists($path), 404);

        // Defence in depth: whatever the path resolves to must sit under the disk
        // root, so a symlink inside the uploads tree cannot escape it.
        $full = realpath($disk->path($path));
        $root = realpath($disk->path(''));

        abort_if($full === false || $root === false, 404);
        abort_unless(str_starts_with($full, rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR), 404);
        abort_unless(is_file($full), 404);

        return $disk->response($path, null, [
            // Upload names are random and never reused, so they cache forever.
            'Cache-Control' => 'public, max-age=31536000',
            // Uploads are user-supplied: never let the browser sniff a different
            // type, and neuter any script an SVG/HTML upload might carry.
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
        ]);
    }
}
