<?php

namespace App\Repositories;

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
