<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function index(): View
    {
        return view('cart.index', [
            'items' => $this->cart->items(),
            'totals' => $this->cart->totals(),
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'seller_id' => ['nullable', 'integer', 'exists:vip_microsites,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'buy_now' => ['nullable', 'boolean'],
        ]);

        $product = Product::where('id', $data['product_id'])->where('status', 'active')->first();

        if (! $product || ! $product->hasPrice()) {
            return back()->with('error', 'Sorry, this product is not available for purchase right now.');
        }

        $this->cart->add($product->id, $data['seller_id'] ?? null, $data['quantity'] ?? 1);

        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.index');
        }

        return back()->with('status', "“{$product->name}” was added to your cart.");
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:40'],
            // Accept 100 so a "+" at the 99 cap is clamped (by the service) rather than erroring.
            'quantity' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $this->cart->setQuantity($data['key'], $data['quantity']);

        return back()->with('status', 'Cart updated.');
    }

    public function remove(Request $request): RedirectResponse
    {
        $data = $request->validate(['key' => ['required', 'string', 'max:40']]);

        $this->cart->remove($data['key']);

        return back()->with('status', 'Item removed from your cart.');
    }
}
