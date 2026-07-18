<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
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

    public function add(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'seller_id' => ['nullable', 'integer', 'exists:vip_microsites,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'buy_now' => ['nullable', 'boolean'],
        ]);

        $product = Product::where('id', $data['product_id'])->where('status', 'active')->first();

        if (! $product || ! $product->hasPrice()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'This product is not available for purchase.'], 422);
            }

            return back()->with('error', 'Sorry, this product is not available for purchase right now.');
        }

        $this->cart->add($product->id, $data['seller_id'] ?? null, $data['quantity'] ?? 1);

        if ($request->wantsJson()) {
            $key = $this->cart->keyFor($product->id, $data['seller_id'] ?? null);

            return response()->json($this->state($key) + ['message' => "“{$product->name}” added to your cart"]);
        }

        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.index');
        }

        return back()->with('status', "“{$product->name}” was added to your cart.");
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:40'],
            'quantity' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $this->cart->setQuantity($data['key'], $data['quantity']);

        if ($request->wantsJson()) {
            return response()->json($this->state($data['key']));
        }

        return back()->with('status', 'Cart updated.');
    }

    public function remove(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate(['key' => ['required', 'string', 'max:40']]);

        $this->cart->remove($data['key']);

        if ($request->wantsJson()) {
            return response()->json($this->state($data['key']));
        }

        return back()->with('status', 'Item removed from your cart.');
    }

    /**
     * The cart state an AJAX caller needs to update the UI in place.
     *
     * @return array<string, mixed>
     */
    private function state(?string $key): array
    {
        $items = $this->cart->items();
        $totals = $this->cart->totals();
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
        $line = $key ? $items->firstWhere('key', $key) : null;

        return [
            'count' => $totals['count'],
            'key' => $key,
            'quantity' => $line['quantity'] ?? 0,
            'line_total' => $line ? $money($line['line_total']) : null,
            'removed' => $key !== null && ! $line,
            'empty' => $items->isEmpty(),
            'totals' => [
                'subtotal' => $money($totals['subtotal']),
                'shipping' => $totals['shipping'] > 0 ? $money($totals['shipping']) : 'Free',
                'total' => $money($totals['total']),
            ],
        ];
    }
}
