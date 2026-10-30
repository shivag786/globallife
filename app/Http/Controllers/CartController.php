<?php

namespace App\Http\Controllers;

use App\Models\BusinessProduct;
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
            'product_id' => ['required_without:business_product_id', 'nullable', 'integer', 'exists:products,id'],
            'business_product_id' => ['required_without:product_id', 'nullable', 'integer', 'exists:business_products,id'],
            'seller_id' => ['nullable', 'integer', 'exists:vip_microsites,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'buy_now' => ['nullable', 'boolean'],
            // The customer agreed to empty a cart holding another shop's items.
            'replace_cart' => ['nullable', 'boolean'],
        ]);

        if (! empty($data['business_product_id'])) {
            return $this->addBusinessProduct($request, $data);
        }

        $product = Product::where('id', $data['product_id'])->where('status', 'active')->first();

        if (! $product || ! $product->hasPrice()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'This product is not available for purchase.'], 422);
            }

            return back()->with('error', 'Sorry, this product is not available for purchase right now.');
        }

        if ($conflict = $this->conflict($request, CartService::TYPE_CATALOG, $data['seller_id'] ?? null)) {
            return $conflict;
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
     * A business's own product. The business, not the request, decides who the
     * seller is, and the package decides whether it may be bought here at all —
     * a catalogue-only page orders on WhatsApp instead.
     *
     * @param  array<string, mixed>  $data
     */
    private function addBusinessProduct(Request $request, array $data): RedirectResponse|JsonResponse
    {
        $product = BusinessProduct::with('vipMicrosite.vipPlan')->find($data['business_product_id']);

        if (! $product || ! $product->isPurchasable()) {
            $message = 'This product cannot be ordered online. Please contact the business directly.';

            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : back()->with('error', $message);
        }

        $sellerId = $product->vip_microsite_id;

        if ($conflict = $this->conflict($request, CartService::TYPE_BUSINESS, $sellerId)) {
            return $conflict;
        }

        $this->cart->addBusiness($product->id, $sellerId, $data['quantity'] ?? 1);

        if ($request->wantsJson()) {
            $key = $this->cart->businessKeyFor($product->id, $sellerId);

            return response()->json($this->state($key) + ['message' => "“{$product->name}” added to your cart"]);
        }

        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.index');
        }

        return back()->with('status', "“{$product->name}” was added to your cart.");
    }

    /**
     * Refuses a line that cannot share the current cart, unless the customer has
     * agreed to empty it first (`replace_cart`). JSON callers get a 409 they can
     * turn into a "Clear cart and add?" prompt.
     */
    private function conflict(Request $request, string $type, ?int $sellerId): RedirectResponse|JsonResponse|null
    {
        $message = $this->cart->conflictFor($type, $sellerId);

        if ($message === null) {
            return null;
        }

        if ($request->boolean('replace_cart')) {
            $this->cart->clear();

            return null;
        }

        return $request->wantsJson()
            ? response()->json(['message' => $message, 'conflict' => true], 409)
            : back()->with('error', $message);
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
