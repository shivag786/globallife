<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\HomeSection;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Storefront-by-category browsing: the homepage shows category image tiles that
 * link to a category page, which lists that category's products with the VIP
 * add-to-cart UX but no seller attribution (so no commission is generated).
 */
class HomeCategoryProductsTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(string $name, string $status = 'active', int $order = 0): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.uniqid(),
            'status' => $status,
            'display_order' => $order,
        ]);
    }

    private function makeProduct(string $name, ?Category $category, string $status = 'active', int $order = 0): Product
    {
        return Product::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.uniqid(),
            'short_description' => 'x',
            'price' => 499,
            'category_id' => $category?->id,
            'status' => $status,
            'display_order' => $order,
        ]);
    }

    public function test_active_categories_with_products_excludes_inactive_and_empty(): void
    {
        $wellness = $this->makeCategory('Wellness', order: 1);
        $fragrance = $this->makeCategory('Fragrance', order: 2);
        $hidden = $this->makeCategory('Hidden', status: 'inactive', order: 0);
        $empty = $this->makeCategory('Empty', order: 3);

        $this->makeProduct('Vitamin C', $wellness);
        $this->makeProduct('Inactive Item', $wellness, status: 'inactive');
        $this->makeProduct('Eau de Parfum', $fragrance);
        $this->makeProduct('Secret Item', $hidden);

        $categories = app(ProductRepository::class)->activeCategoriesWithProducts();

        // Inactive category and the category with no active products are dropped, ordered.
        $this->assertSame(['Wellness', 'Fragrance'], $categories->pluck('name')->all());
        // products_count reflects only ACTIVE products.
        $this->assertSame(1, $categories->firstWhere('name', 'Wellness')->products_count);
    }

    public function test_homepage_renders_category_tiles_linking_to_category_page(): void
    {
        $category = $this->makeCategory('Wellness');
        $this->makeProduct('Vitamin C Serum', $category);

        HomeSection::create([
            'type' => 'category_products',
            'title' => 'Shop by Category',
            'status' => 'active',
            'display_order' => 1,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Shop by Category')
            ->assertSee('Wellness')
            ->assertSee(route('products.category', $category), false)
            // Tiles link out — they don't add to cart themselves.
            ->assertDontSee(route('cart.add'), false);
    }

    public function test_category_page_lists_its_products_with_add_to_cart(): void
    {
        $category = $this->makeCategory('Wellness');
        $other = $this->makeCategory('Fragrance');
        $product = $this->makeProduct('Vitamin C Serum', $category);
        $this->makeProduct('Perfume', $other);
        $this->makeProduct('Hidden Serum', $category, status: 'inactive');

        $response = $this->get(route('products.category', $category));

        $response->assertOk()
            ->assertSee('Vitamin C Serum')
            ->assertDontSee('Perfume')            // other category not shown
            ->assertDontSee('Hidden Serum')       // inactive not shown
            // Direct add-to-cart form, no seller_id → no commission.
            ->assertSee(route('cart.add'), false)
            ->assertSee('name="product_id" value="'.$product->id.'"', false)
            ->assertDontSee('name="seller_id"', false);
    }

    public function test_category_page_404s_for_inactive_category(): void
    {
        $category = $this->makeCategory('Hidden', status: 'inactive');
        $this->makeProduct('Vitamin C', $category);

        $this->get(route('products.category', $category))->assertNotFound();
    }

    public function test_grouped_by_category_excludes_empty_and_respects_per_category_limit(): void
    {
        $wellness = $this->makeCategory('Wellness', order: 1);
        $empty = $this->makeCategory('Empty', order: 2);
        foreach (range(1, 5) as $i) {
            $this->makeProduct("Product {$i}", $wellness, order: $i);
        }

        $grouped = app(ProductRepository::class)->groupedByCategory(perCategory: 3);

        $this->assertSame(['Wellness'], $grouped->pluck('name')->all());
        $this->assertSame(3, $grouped->firstWhere('name', 'Wellness')->products->count());
    }

    public function test_homepage_renders_grouped_products_section_with_add_to_cart(): void
    {
        $category = $this->makeCategory('Wellness');
        $product = $this->makeProduct('Vitamin C Serum', $category);

        HomeSection::create([
            'type' => 'products_by_category',
            'title' => 'Shop Our Products',
            'status' => 'active',
            'display_order' => 3,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Shop Our Products')
            ->assertSee('Vitamin C Serum')
            // Grouped cards add to cart directly, with no seller → no commission.
            ->assertSee(route('cart.add'), false)
            ->assertSee('name="product_id" value="'.$product->id.'"', false)
            ->assertDontSee('name="seller_id"', false);
    }

    public function test_products_index_has_add_to_cart(): void
    {
        $product = $this->makeProduct('Vitamin C Serum', $this->makeCategory('Wellness'));

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Vitamin C Serum')
            ->assertSee(route('cart.add'), false)
            ->assertSee('name="product_id" value="'.$product->id.'"', false);
    }
}
