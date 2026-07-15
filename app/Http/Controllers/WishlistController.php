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
        return view('wishlist.index', ['items' => $this->wishlist->items()]);
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate(['product_id' => ['required', 'integer', 'exists:products,id']]);

        $this->wishlist->add((int) $data['product_id']);

        return back()->with('status', 'Saved to your wishlist.');
    }

    public function remove(Request $request): RedirectResponse
    {
        $data = $request->validate(['product_id' => ['required', 'integer']]);

        $this->wishlist->remove((int) $data['product_id']);

        return back()->with('status', 'Removed from your wishlist.');
    }
}
