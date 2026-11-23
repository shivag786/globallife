<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CompanyProductsSwitch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Admin page with the one switch that hides the company's own products
 * everywhere (CompanyProductsSwitch). Reached by URL only — no menu link.
 */
class CompanyProductsController extends Controller
{
    public function __construct(private readonly CompanyProductsSwitch $switch) {}

    public function show(): View
    {
        return view('admin.settings.company-products', [
            'hidden' => $this->switch->hidden(),
            'activeProducts' => Product::where('status', 'active')->count(),
        ]);
    }

    /**
     * Flip the switch. Nothing is deleted either way, so it is fully reversible.
     */
    public function toggle(): RedirectResponse
    {
        $hide = ! $this->switch->hidden();
        $this->switch->setHidden($hide);

        activity()->causedBy(auth()->user())
            ->withProperties(['company_products_hidden' => $hide])
            ->log($hide ? 'Hid company products' : 'Showed company products');

        return redirect()->route('admin.settings.company-products.show')->with('status', $hide
            ? 'Company products are now hidden everywhere.'
            : 'Company products are back on sale.');
    }
}
