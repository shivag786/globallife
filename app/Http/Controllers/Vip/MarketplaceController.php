<?php

namespace App\Http\Controllers\Vip;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CompanyProductsSwitch;
use App\Services\ProductCommissionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarketplaceController extends Controller
{
    public function __construct(private readonly ProductCommissionService $commissions) {}

    public function index(): View
    {
        $microsite = Auth::user()->vipMicrosite;
        $products = Product::onSale()->with('category')->orderBy('name')->get();

        return view('vip.marketplace.index', [
            'microsite' => $microsite,
            'products' => $products,
            'pivots' => DB::table('vip_products')->where('vip_microsite_id', $microsite->id)->get()->keyBy('product_id'),
            'earnings' => $this->earningsPerProduct($products),
            'companyProductsHidden' => app(CompanyProductsSwitch::class)->hidden(),
        ]);
    }

    public function update(Request $request, CompanyProductsSwitch $switch): RedirectResponse
    {
        $microsite = Auth::user()->vipMicrosite;

        // While company products are off sale there is nothing to choose from,
        // and syncing an empty list would wipe this member's saved choices.
        if ($switch->hidden()) {
            return redirect()->route('vip.marketplace.index')
                ->with('error', 'Company products are not on sale right now, so your store cannot be changed.');
        }

        $validated = $request->validate([
            'products' => ['nullable', 'array'],
            'products.*.show' => ['nullable', 'boolean'],
            'products.*.featured' => ['nullable', 'boolean'],
            'products.*.order' => ['nullable', 'integer', 'min:0'],
        ]);

        $selections = $validated['products'] ?? [];
        $sync = [];

        // The catalog sells by default, so a row is saved for every active product
        // carrying whatever this member chose — including is_visible = false, which
        // is the only thing that takes a product off their page. The VIP controls
        // visibility, featured and order only; price, description and commission
        // stay Super-Admin owned.
        foreach (Product::onSale()->pluck('id') as $productId) {
            $row = $selections[$productId] ?? [];

            $sync[$productId] = [
                'is_visible' => ! empty($row['show']),
                'is_featured' => ! empty($row['featured']),
                'display_order' => (int) ($row['order'] ?? 0),
            ];
        }

        $microsite->catalogProducts()->sync($sync);

        return redirect()->route('vip.marketplace.index')->with('status', 'Your store has been updated.');
    }

    /**
     * What this member earns per unit sold, per product.
     *
     * Read straight off the same rules that pay a real order
     * (`ProductCommissionService`), so the calculator on the page cannot promise
     * a rate the ledger would not honour.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, Product>  $products
     * @return array<int, array{price: float, percent: ?float, type: ?string, amount: float}>
     */
    private function earningsPerProduct($products): array
    {
        $out = [];

        foreach ($products as $product) {
            $price = (float) ($product->sellingPrice() ?? 0);
            $rule = $this->commissions->resolveRule($product, 'vip_member');

            $out[$product->id] = [
                'price' => $price,
                'percent' => $rule && $rule->type === 'percent' ? (float) $rule->value : null,
                'type' => $rule?->type,
                'amount' => $this->commissions->ruleAmount($rule, $price),
            ];
        }

        return $out;
    }
}
