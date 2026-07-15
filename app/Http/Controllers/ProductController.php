<?php

namespace App\Http\Controllers;

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
