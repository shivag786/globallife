<?php

namespace Tests\Feature;

use App\Support\UploadsRoot;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Uploads must survive a deploy.
 *
 * Production deploys by rebuilding the deploy directory from git, so a file
 * uploaded after the last commit -- a re-uploaded product image -- was deleted
 * by the next push, while older images that happened to be committed kept
 * coming back. Uploads now live in a sibling of the deploy directory by
 * default outside local/testing, and files still sitting in the old in-project
 * folder are adopted into the new location the first time they are requested.
 */
class UploadsSurviveDeploysTest extends TestCase
{
    private string $sandbox;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sandbox = sys_get_temp_dir().DIRECTORY_SEPARATOR.'gl-uploads-'.uniqid();
        File::ensureDirectoryExists($this->sandbox.DIRECTORY_SEPARATOR.'public_html');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->sandbox);

        parent::tearDown();
    }

    private function deployDir(): string
    {
        return $this->sandbox.DIRECTORY_SEPARATOR.'public_html';
    }

    /* ------------------------------------------------------------ resolver */

    public function test_an_explicit_root_always_wins(): void
    {
        $this->assertSame(
            '/srv/uploads',
            UploadsRoot::resolve('/srv/uploads/', 'production', $this->deployDir()),
        );
    }

    public function test_production_uses_a_sibling_of_the_deploy_directory(): void
    {
        $root = UploadsRoot::resolve('', 'production', $this->deployDir());

        $this->assertSame($this->sandbox.DIRECTORY_SEPARATOR.'public_html-uploads', $root);
        $this->assertDirectoryExists($root, 'it is created on first use');

        // The whole point: it is not inside the directory a deploy rebuilds.
        $this->assertStringStartsNotWith($this->deployDir().DIRECTORY_SEPARATOR, $root.DIRECTORY_SEPARATOR);
    }

    public function test_an_existing_persistent_folder_is_reused(): void
    {
        $existing = $this->sandbox.DIRECTORY_SEPARATOR.'public_html-uploads';
        File::ensureDirectoryExists($existing);
        File::put($existing.DIRECTORY_SEPARATOR.'keep.txt', 'x');

        $this->assertSame($existing, UploadsRoot::resolve(null, 'production', $this->deployDir()));
        $this->assertFileExists($existing.DIRECTORY_SEPARATOR.'keep.txt');
    }

    public function test_local_and_testing_keep_the_in_project_folder(): void
    {
        $this->assertSame(UploadsRoot::legacy(), UploadsRoot::resolve('', 'local', $this->deployDir()));
        $this->assertSame(UploadsRoot::legacy(), UploadsRoot::resolve('', 'testing', $this->deployDir()));
    }

    public function test_an_unusable_parent_falls_back_rather_than_breaking_uploads(): void
    {
        // A deploy directory whose parent does not exist cannot get a sibling.
        $nowhere = $this->sandbox.DIRECTORY_SEPARATOR.'missing'.DIRECTORY_SEPARATOR.'deep'.DIRECTORY_SEPARATOR.'public_html';

        // mkdir is recursive, so make the parent genuinely unusable: a FILE.
        File::put($this->sandbox.DIRECTORY_SEPARATOR.'missing', 'not a directory');

        $this->assertSame(UploadsRoot::legacy(), UploadsRoot::resolve('', 'production', $nowhere));
    }

    /* ------------------------------------------------- legacy adoption */

    public function test_a_file_left_in_the_old_folder_is_adopted_on_first_request(): void
    {
        $newRoot = $this->sandbox.DIRECTORY_SEPARATOR.'public_html-uploads';
        File::ensureDirectoryExists($newRoot);

        // Point the public disk somewhere new, as production now does.
        config(['filesystems.disks.public.root' => $newRoot]);
        Storage::forgetDisk('public');

        // A committed upload still arriving in the in-project folder.
        $legacyFile = UploadsRoot::legacy().DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'adopt-me-'.uniqid().'.png';
        File::ensureDirectoryExists(dirname($legacyFile));
        File::put($legacyFile, 'png-bytes');
        $relative = 'uploads/'.basename($legacyFile);

        try {
            $this->get('/storage/'.$relative)->assertOk();

            // Copied into the new location, so the next deploy cannot take it.
            $this->assertFileExists($newRoot.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.basename($legacyFile));
            $this->assertSame('png-bytes', File::get($newRoot.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.basename($legacyFile)));
        } finally {
            File::delete($legacyFile);
        }
    }

    public function test_adoption_cannot_be_used_to_read_outside_the_old_folder(): void
    {
        $newRoot = $this->sandbox.DIRECTORY_SEPARATOR.'public_html-uploads';
        File::ensureDirectoryExists($newRoot);
        config(['filesystems.disks.public.root' => $newRoot]);
        Storage::forgetDisk('public');

        $this->get('/storage/../.env')->assertNotFound();
        $this->get('/storage/uploads/..%2F..%2F.env')->assertNotFound();
        $this->get('/storage/uploads/does-not-exist.png')->assertNotFound();
    }

    public function test_a_new_upload_is_written_to_the_persistent_folder(): void
    {
        $newRoot = $this->sandbox.DIRECTORY_SEPARATOR.'public_html-uploads';
        File::ensureDirectoryExists($newRoot);
        config(['filesystems.disks.public.root' => $newRoot]);
        Storage::forgetDisk('public');

        $path = Storage::disk('public')->put('uploads', \Illuminate\Http\UploadedFile::fake()->image('p.png'));

        $this->assertFileExists($newRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path));
        $this->assertFileDoesNotExist(UploadsRoot::legacy().DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path));
    }
}
