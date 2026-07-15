<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCommissionRulesRequest;
use App\Models\Category;
use App\Models\CommissionRule;
use App\Models\Product;
use App\Services\ProductCommissionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CommissionController extends Controller
{
    public function __construct(private readonly ProductCommissionService $commission)
    {
    }

    public function index(): View
    {
        return view('admin.commissions.index', [
            'roles' => ProductCommissionService::EARNING_ROLES,
            'global' => $this->currentRules('global', 0),
            'categories' => Category::orderBy('name')->get(),
            'products' => Product::with('category')->orderBy('name')->get(),
            'categoryScoped' => CommissionRule::where('scope', 'category')->pluck('scope_id'),
            'productScoped' => CommissionRule::where('scope', 'product')->pluck('scope_id'),
        ]);
    }

    public function editGlobal(): View
    {
        return view('admin.commissions.global', [
            'roles' => ProductCommissionService::EARNING_ROLES,
            'current' => $this->currentRules('global', 0),
        ]);
    }

    public function updateGlobal(UpdateCommissionRulesRequest $request): RedirectResponse
    {
        $this->syncRules('global', 0, $request->input('rules', []));

        return redirect()->route('admin.commissions.index')->with('status', 'Global commission defaults saved.');
    }

    public function editCategory(Category $category): View
    {
        return view('admin.commissions.category', [
            'roles' => ProductCommissionService::EARNING_ROLES,
            'category' => $category,
            'current' => $this->currentRules('category', $category->id),
        ]);
    }

    public function updateCategory(UpdateCommissionRulesRequest $request, Category $category): RedirectResponse
    {
        $this->syncRules('category', $category->id, $request->input('rules', []));

        return redirect()->route('admin.commissions.index')->with('status', "Commission for “{$category->name}” saved.");
    }

    public function editProduct(Product $product): View
    {
        $base = (float) ($product->sellingPrice() ?? 0);

        return view('admin.commissions.product', [
            'roles' => ProductCommissionService::EARNING_ROLES,
            'product' => $product,
            'current' => $this->currentRules('product', $product->id),
            'preview' => $this->commission->previewSplit($product, $base),
        ]);
    }

    public function updateProduct(UpdateCommissionRulesRequest $request, Product $product): RedirectResponse
    {
        $this->syncRules('product', $product->id, $request->input('rules', []));

        return redirect()->route('admin.commissions.index')->with('status', "Commission for “{$product->name}” saved.");
    }

    /**
     * Current rules for a scope target, keyed by every earning role (null when unset).
     *
     * @return array<string, CommissionRule|null>
     */
    private function currentRules(string $scope, int $scopeId): array
    {
        $rules = CommissionRule::where('scope', $scope)->where('scope_id', $scopeId)->get()->keyBy('role');

        $out = [];
        foreach (array_keys(ProductCommissionService::EARNING_ROLES) as $role) {
            $out[$role] = $rules->get($role);
        }

        return $out;
    }

    /**
     * Upsert a rule per role from the submitted values; a blank value removes the
     * rule so that scope falls back to a less-specific one.
     *
     * @param  array<string, array{type?: string, value?: mixed}>  $input
     */
    private function syncRules(string $scope, int $scopeId, array $input): void
    {
        foreach (array_keys(ProductCommissionService::EARNING_ROLES) as $role) {
            $row = $input[$role] ?? [];
            $value = $row['value'] ?? null;

            if ($value === null || $value === '') {
                CommissionRule::where('scope', $scope)->where('scope_id', $scopeId)->where('role', $role)->delete();

                continue;
            }

            CommissionRule::updateOrCreate(
                ['scope' => $scope, 'scope_id' => $scopeId, 'role' => $role],
                ['type' => $row['type'] ?? 'percent', 'value' => $value, 'status' => 'active'],
            );
        }
    }
}
