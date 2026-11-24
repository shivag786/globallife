<?php

namespace App\Http\Controllers;

use App\Services\WishlistService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(private readonly WishlistService $wishlist)
    {
    }

    public function index(): View
    {
        return view('wishlist.index', [
            'items' => $this->wishlist->items(),
            'businessItems' => $this->wishlist->businessItems(),
        ]);
    }

    /**
     * Saves either a company catalogue product (`product_id`) or a VIP
     * business's own product from its microsite (`business_product_id`).
     */
    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required_without:business_product_id', 'nullable', 'integer', 'exists:products,id'],
            'business_product_id' => ['required_without:product_id', 'nullable', 'integer', 'exists:business_products,id'],
        ]);

        if (! empty($data['business_product_id'])) {
            $this->wishlist->addBusiness((int) $data['business_product_id']);
        } else {
            $this->wishlist->add((int) $data['product_id']);
        }

        return back()->with('status', 'Saved to your wishlist.');
    }

    public function remove(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required_without:business_product_id', 'nullable', 'integer'],
            'business_product_id' => ['required_without:product_id', 'nullable', 'integer'],
        ]);

        if (! empty($data['business_product_id'])) {
            $this->wishlist->removeBusiness((int) $data['business_product_id']);
        } else {
            $this->wishlist->remove((int) $data['product_id']);
        }

        return back()->with('status', 'Removed from your wishlist.');
    }
}
