<?php

namespace App\Http\Controllers\Vip;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarketplaceController extends Controller
{
    public function index(): View
    {
        $microsite = Auth::user()->vipMicrosite;

        return view('vip.marketplace.index', [
            'microsite' => $microsite,
            'products' => Product::where('status', 'active')->with('category')->orderBy('name')->get(),
            'pivots' => DB::table('vip_products')->where('vip_microsite_id', $microsite->id)->get()->keyBy('product_id'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $microsite = Auth::user()->vipMicrosite;

        $validated = $request->validate([
            'products' => ['nullable', 'array'],
            'products.*.show' => ['nullable', 'boolean'],
            'products.*.featured' => ['nullable', 'boolean'],
            'products.*.order' => ['nullable', 'integer', 'min:0'],
        ]);

        $selections = $validated['products'] ?? [];
        $sync = [];

        // Only real, active products can be added — the VIP controls visibility, featured
        // and order only, never price/description/commission (those stay Super-Admin owned).
        foreach (Product::where('status', 'active')->pluck('id') as $productId) {
            $row = $selections[$productId] ?? [];
            if (! empty($row['show'])) {
                $sync[$productId] = [
                    'is_visible' => true,
                    'is_featured' => ! empty($row['featured']),
                    'display_order' => (int) ($row['order'] ?? 0),
                ];
            }
        }

        $microsite->catalogProducts()->sync($sync);

        return redirect()->route('vip.marketplace.index')->with('status', 'Your store has been updated.');
    }
}
