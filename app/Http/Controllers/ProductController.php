<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\VipMicrosite;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(ProductRepository $products): View
    {
        return view('products.index', ['products' => $products->publishedPaginated()]);
    }

    /**
     * All active products within a single category — direct add-to-cart, no seller.
     */
    public function category(Category $category, ProductRepository $products): View
    {
        abort_unless($category->status === 'active', 404);

        return view('products.category', [
            'category' => $category,
            'products' => $products->publishedInCategory($category),
        ]);
    }

    public function show(Product $product, ProductRepository $products, Request $request): View
    {
        abort_unless($product->status === 'active', 404);

        $product->load(['benefits' => fn ($query) => $query->where('status', 'active')->orderBy('display_order')]);

        // Carry the selling VIP's storefront context (?store=) so an add-to-cart from
        // here still attributes commission to the right seller.
        $seller = null;
        if ($storeId = $request->query('store')) {
            $seller = VipMicrosite::where('id', $storeId)->where('status', 'active')->first();
        }

        return view('products.show', [
            'product' => $product,
            'related' => $products->related($product),
            'seller' => $seller,
        ]);
    }
}
