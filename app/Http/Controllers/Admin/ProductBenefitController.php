<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductBenefitRequest;
use App\Http\Requests\Admin\UpdateProductBenefitRequest;
use App\Models\Product;
use App\Models\ProductBenefit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProductBenefitController extends Controller
{
    public function index(Product $product): View
    {
        $this->authorizeEdit();

        $editing = request('edit')
            ? $product->benefits()->find(request('edit'))
            : null;

        return view('admin.products.benefits.index', [
            'product' => $product,
            'benefits' => $product->benefits()->orderBy('display_order')->orderBy('id')->get(),
            'editing' => $editing,
        ]);
    }

    public function store(StoreProductBenefitRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('uploads', 'public');
        }

        $product->benefits()->create($data);

        return redirect()->route('admin.products.benefits.index', $product)->with('status', 'Benefit added.');
    }

    public function update(UpdateProductBenefitRequest $request, Product $product, ProductBenefit $benefit): RedirectResponse
    {
        abort_unless($benefit->product_id === $product->id, 404);

        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('uploads', 'public');
        }

        $benefit->update($data);

        return redirect()->route('admin.products.benefits.index', $product)->with('status', 'Benefit updated.');
    }

    public function destroy(Product $product, ProductBenefit $benefit): RedirectResponse
    {
        $this->authorizeEdit();
        abort_unless($benefit->product_id === $product->id, 404);

        $benefit->delete();

        return redirect()->route('admin.products.benefits.index', $product)->with('status', 'Benefit deleted.');
    }

    private function authorizeEdit(): void
    {
        abort_unless(auth()->user()->can('products.edit'), 403);
    }
}
