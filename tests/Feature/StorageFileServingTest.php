<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * /storage/{path} must keep working even with no public/storage symlink.
 *
 * Production deploys by clean checkout, which can delete that symlink (it is
 * gitignored, so the repo does not carry it) and take every image on the site
 * down. This route is the safety net.
 */
class StorageFileServingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Write a real file on the configured public disk and return its path.
     */
    private function putRealFile(string $path, string $contents = 'binary-image-bytes'): string
    {
        $full = Storage::disk('public')->path($path);
        File::ensureDirectoryExists(dirname($full));
        File::put($full, $contents);

        return $full;
    }

    public function test_an_uploaded_file_is_served_through_the_route(): void
    {
        $full = $this->putRealFile('uploads/route-served.txt', 'hello-from-storage');

        try {
            $response = $this->get('/storage/uploads/route-served.txt');

            $response->assertOk();
            $this->assertSame('hello-from-storage', $response->streamedContent());
            $response->assertHeader('X-Content-Type-Options', 'nosniff');
            $this->assertStringContainsString('max-age', (string) $response->headers->get('Cache-Control'));
        } finally {
            File::delete($full);
        }
    }

    public function test_a_missing_file_is_a_404_not_a_server_error(): void
    {
        $this->get('/storage/uploads/definitely-not-here.webp')->assertNotFound();
    }

    public function test_path_traversal_cannot_escape_the_disk_root(): void
    {
        // .env sits two levels above the public disk root.
        $this->get('/storage/../../.env')->assertNotFound();
        $this->get('/storage/uploads/../../../.env')->assertNotFound();
    }

    public function test_a_directory_is_not_served(): void
    {
        $full = $this->putRealFile('uploads/dir-probe/file.txt');

        try {
            $this->get('/storage/uploads/dir-probe')->assertNotFound();
        } finally {
            File::deleteDirectory(dirname($full));
        }
    }

    public function test_the_route_does_not_shadow_the_public_microsite_route(): void
    {
        // The microsite catch-all is /{city}/{business}/{secureId}; the storage
        // route is registered above it and must not swallow those URLs.
        $this->assertSame(
            'microsite.show',
            app('router')->getRoutes()->match(
                Request::create('/jhansi/lifeline-hospital/22-LSTWEFF-44', 'GET')
            )->getName(),
        );

        $this->assertSame(
            'storage.file',
            app('router')->getRoutes()->match(
                Request::create('/storage/uploads/a.webp', 'GET')
            )->getName(),
        );
    }

    public function test_the_public_disk_root_follows_the_env_override(): void
    {
        // Production points this outside the deploy directory so a clean checkout
        // cannot delete uploads. Default stays storage/app/public for local dev.
        $this->assertSame(
            storage_path('app/public'),
            rtrim(config('filesystems.disks.public.root'), DIRECTORY_SEPARATOR),
        );

        $this->assertSame(
            config('filesystems.disks.public.root'),
            config('filesystems.links.'.public_path('storage')) ?? config('filesystems.disks.public.root'),
        );
    }
}
