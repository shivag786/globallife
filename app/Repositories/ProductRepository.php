<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    /**
     * @return Collection<int, Product>
     */
    public function allOrdered(): Collection
    {
        return Product::orderBy('display_order')->get();
    }

    public function publishedPaginated(int $perPage = 12): LengthAwarePaginator
    {
        return Product::where('status', 'active')->orderBy('display_order')->paginate($perPage);
    }

    /**
     * @return Collection<int, Product>
     */
    public function featured(int $limit = 4): Collection
    {
        return Product::where('status', 'active')->where('is_featured', true)
            ->orderBy('display_order')->limit($limit)->get();
    }

    /**
     * Other active products to show alongside the one being viewed — same
     * category first, then filled out with other products if needed.
     *
     * @return Collection<int, Product>
     */
    public function related(Product $product, int $limit = 4): Collection
    {
        return Product::where('status', 'active')
            ->whereKeyNot($product->getKey())
            ->orderByRaw('CASE WHEN category = ? THEN 0 ELSE 1 END', [$product->category])
            ->orderBy('display_order')
            ->limit($limit)
            ->get();
    }

    /**
     * Active categories that have at least one purchasable product, each carrying
     * a live `products_count` — drives the homepage "category_products" tiles.
     * Categories with no active products are dropped.
     *
     * @return Collection<int, Category>
     */
    public function activeCategoriesWithProducts(): Collection
    {
        return Category::active()
            ->whereHas('products', fn ($query) => $query->where('status', 'active'))
            ->withCount(['products' => fn ($query) => $query->where('status', 'active')])
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Active categories that have purchasable products, each carrying up to
     * $perCategory active products (ordered) — drives the homepage
     * "products_by_category" section. Categories with no active products are dropped.
     *
     * @return Collection<int, Category>
     */
    public function groupedByCategory(int $perCategory = 8): Collection
    {
        return Category::active()
            ->whereHas('products', fn ($query) => $query->where('status', 'active'))
            ->with(['products' => fn ($query) => $query->where('status', 'active')->orderBy('display_order')])
            ->orderBy('display_order')
            ->get()
            ->each(fn (Category $category) => $category->setRelation('products', $category->products->take($perCategory)))
            ->filter(fn (Category $category) => $category->products->isNotEmpty())
            ->values();
    }

    /**
     * Active products within a category, ordered and paginated — the category page.
     */
    public function publishedInCategory(Category $category, int $perPage = 12): LengthAwarePaginator
    {
        return Product::where('status', 'active')
            ->where('category_id', $category->id)
            ->orderBy('display_order')
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product;
    }
}
