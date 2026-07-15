<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\PermissionMatrixService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProductController extends Controller
{
    public function __construct(private readonly ProductRepository $products)
    {
    }

    public function index(): View
    {
        $this->ensureModuleAccess();

        return view('admin.products.index', ['products' => $this->products->allOrdered()]);
    }

    public function create(): View
    {
        $this->ensureModuleAccess();

        return view('admin.products.create');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $this->withCatalogData($request, $request->validated(), []);

        $this->products->create($data);

        return redirect()->route('admin.products.index')->with('status', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $this->ensureModuleAccess();

        return view('admin.products.edit', ['product' => $product]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $this->withCatalogData($request, $request->validated(), $product->gallery ?? []);

        $this->products->update($product, $data);

        return redirect()->route('admin.products.index')->with('status', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        abort_unless(auth()->user()->can('products.delete'), 403);

        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    /**
     * Applies main image, gallery images, and legacy category-name sync to the
     * validated payload before persisting.
     *
     * @param  array<string, mixed>  $data
     * @param  list<string>  $existingGallery
     * @return array<string, mixed>
     */
    private function withCatalogData(Request $request, array $data, array $existingGallery): array
    {
        if ($request->hasFile('main_image')) {
            $data['main_image'] = $request->file('main_image')->store('uploads', 'public');
        }

        $gallery = $request->boolean('remove_gallery') ? [] : $existingGallery;
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $gallery[] = $file->store('uploads', 'public');
            }
        }
        $data['gallery'] = $gallery;

        // Keep the legacy `category` string in sync with the selected category so the
        // existing storefront (which reads $product->category) keeps working unchanged.
        if (! empty($data['category_id'])) {
            $data['category'] = Category::find($data['category_id'])?->name;
        }

        return $data;
    }

    private function ensureModuleAccess(): void
    {
        if (! PermissionMatrixService::userCanAccessModule(auth()->user(), 'products')) {
            throw new AccessDeniedHttpException;
        }
    }
}
