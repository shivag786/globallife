<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\View\View;

class ProductController extends Controller
{
    public function index(ProductRepository $products): View
    {
        return view('products.index', ['products' => $products->publishedPaginated()]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->status === 'active', 404);

        return view('products.show', ['product' => $product]);
    }
}
