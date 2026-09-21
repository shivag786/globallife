<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * The category image saves correctly AND actually reaches the public page.
 * It used to be stored fine but rendered nowhere except the admin list and the
 * homepage category grid, which read as "the upload is broken".
 */
class CategoryImageUploadTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    private function admin(): User
    {
        $this->seedRoles();
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_creating_a_category_persists_the_uploaded_image_path(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Skin Care',
            'status' => 'active',
            'image' => UploadedFile::fake()->image('skin.jpg', 400, 400),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.categories.index'));

        $category = Category::where('name', 'Skin Care')->sole();

        $this->assertNotNull($category->image, 'categories.image should hold the uploaded path');
        Storage::disk('public')->assertExists($category->image);
    }

    public function test_updating_a_category_persists_a_newly_uploaded_image_path(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $category = Category::create([
            'name' => 'Soaps', 'slug' => 'soaps', 'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->put("/admin/categories/{$category->id}", [
            'name' => 'Soaps',
            'status' => 'active',
            'image' => UploadedFile::fake()->image('soap.jpg', 400, 400),
        ]);

        $response->assertSessionHasNoErrors();

        $category->refresh();

        $this->assertNotNull($category->image, 'categories.image should hold the uploaded path after update');
        Storage::disk('public')->assertExists($category->image);
    }

    public function test_the_public_category_page_renders_the_image(): void
    {
        $this->admin();

        $category = Category::create([
            'name' => 'Herbal Wellness', 'slug' => 'herbal-wellness', 'status' => 'active',
            'image' => 'uploads/herbal-hero.webp',
        ]);

        $this->get(route('products.category', $category))
            ->assertOk()
            ->assertSee('storage/uploads/herbal-hero.webp', false);
    }

    public function test_a_failed_category_save_reports_why_instead_of_bouncing_silently(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        // 3 MB is over the 2 MB rule; the admin must be told, not left guessing.
        $response = $this->actingAs($admin)
            ->from(route('admin.categories.create'))
            ->followingRedirects()
            ->post('/admin/categories', [
                'name' => 'Too Big',
                'status' => 'active',
                'image' => UploadedFile::fake()->create('huge.jpg', 3072, 'image/jpeg'),
            ]);

        $response->assertOk();
        // The summary renders, naming the offending field.
        $response->assertSee('Couldn', false);
        $response->assertSee('image', false);

        $this->assertSame(0, Category::where('name', 'Too Big')->count());
    }
}
