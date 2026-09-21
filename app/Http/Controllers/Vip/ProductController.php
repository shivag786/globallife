<?php

namespace App\Http\Controllers\Vip;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vip\StoreProductRequest;
use App\Http\Requests\Vip\UpdateProductRequest;
use App\Models\BusinessProduct;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Auth::user()->vipMicrosite->products()->get();

        return view('vip.products.index', [
            'products' => $products,
            'quota' => Auth::user()->vipMicrosite->contentQuota('products'),
        ]);
    }

    public function create(): View|RedirectResponse
    {
        if ($blocked = $this->quotaBlock()) {
            return $blocked;
        }

        return view('vip.products.create', ['quota' => Auth::user()->vipMicrosite->contentQuota('products')]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        if ($blocked = $this->quotaBlock()) {
            return $blocked;
        }

        $data = $this->prepareData($request);
        $data['vip_microsite_id'] = Auth::user()->vipMicrosite->id;

        BusinessProduct::create($data);

        return redirect()->route('vip.products.index')->with('status', 'Product created successfully.');
    }

    public function edit(BusinessProduct $product): View
    {
        abort_unless($product->vip_microsite_id === Auth::user()->vipMicrosite->id, 403);

        return view('vip.products.edit', ['product' => $product]);
    }

    public function update(UpdateProductRequest $request, BusinessProduct $product): RedirectResponse
    {
        $product->update($this->prepareData($request));

        return redirect()->route('vip.products.index')->with('status', 'Product updated successfully.');
    }

    public function destroy(BusinessProduct $product): RedirectResponse
    {
        abort_unless($product->vip_microsite_id === Auth::user()->vipMicrosite->id, 403);

        $product->delete();

        return redirect()->route('vip.products.index')->with('status', 'Product deleted.');
    }

    /**
     * Refuses a new product once the member's package cap is reached. Their existing
     * rows stay fully editable — only adding is blocked until they renew onto a
     * bigger package.
     */
    private function quotaBlock(): ?RedirectResponse
    {
        $quota = Auth::user()->vipMicrosite->contentQuota('products');

        if ($quota['can_add']) {
            return null;
        }

        return redirect()->route('vip.products.index')->with('error', sprintf(
            'Your plan allows %d product%s and you already have %d. Ask your Commission Partner to renew you onto a larger package to add more.',
            $quota['limit'],
            $quota['limit'] === 1 ? '' : 's',
            $quota['used'],
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareData(StoreProductRequest|UpdateProductRequest $request): array
    {
        $data = $request->validated();
        $data['slug'] = $request->input('slug');
        $data['tags'] = $data['tags'] ?? null ? array_map('trim', explode(',', $data['tags'])) : null;
        $data['show_pricing'] = $request->boolean('show_pricing');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['meta_title'] = $data['name'];
        $data['meta_description'] = $data['short_description'] ?? Str::limit(strip_tags($data['long_description'] ?? ''), 160);
        $data['meta_keywords'] = $data['category'] ?? null;

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('uploads', 'public');
        }

        return $data;
    }
}
