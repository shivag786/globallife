<?php

namespace App\Services;

use App\Models\CommissionRule;
use App\Models\Product;
use App\Models\User;

/**
 * The product-sale commission engine — fully separate from the VIP-activation
 * (joining) commission system. All amounts are computed on the backend from
 * database-driven rules; nothing is hardcoded and no level count is assumed.
 */
class ProductCommissionService
{
    /**
     * Beneficiary roles that can earn product commission, in display order.
     * The company (super admin) always takes the remainder — it is not a rule.
     *
     * @var array<string, string>
     */
    public const EARNING_ROLES = [
        'vip_member' => 'VIP Member',
        'commission_partner' => 'Commission Partner',
        'branch_manager' => 'Branch Manager',
    ];

    /**
     * Resolve the most-specific active rule for a product + role:
     * product-scope → category-scope → global-scope → none.
     */
    public function resolveRule(Product $product, string $role): ?CommissionRule
    {
        $rule = CommissionRule::active()->where('role', $role)
            ->where('scope', 'product')->where('scope_id', $product->id)->first();

        if (! $rule && $product->category_id) {
            $rule = CommissionRule::active()->where('role', $role)
                ->where('scope', 'category')->where('scope_id', $product->category_id)->first();
        }

        if (! $rule) {
            $rule = CommissionRule::active()->where('role', $role)
                ->where('scope', 'global')->where('scope_id', 0)->first();
        }

        return $rule;
    }

    /**
     * Amount a rule yields against a base. Percentages are clamped to 0–100 and
     * fixed amounts to the base, so a beneficiary can never earn more than the sale.
     */
    public function ruleAmount(?CommissionRule $rule, float $base): float
    {
        if (! $rule || $base <= 0) {
            return 0.0;
        }

        $amount = $rule->type === 'percent'
            ? $base * (min((float) $rule->value, 100) / 100)
            : (float) $rule->value;

        return round(max(0.0, min($amount, $base)), 2);
    }

    /**
     * Preview the split for a product at a base amount, assuming every role is
     * present. Powers the admin panel's live preview. Company = remainder.
     *
     * @return array{base: float, lines: array<int, array{role: string, label: string, source: string, type: ?string, value: ?float, amount: float}>, company: float}
     */
    public function previewSplit(Product $product, float $base): array
    {
        $lines = [];
        $paid = 0.0;

        foreach (self::EARNING_ROLES as $role => $label) {
            $rule = $this->resolveRule($product, $role);
            $amount = $this->ruleAmount($rule, $base);
            $lines[] = [
                'role' => $role,
                'label' => $label,
                'source' => $rule?->scope ?? 'none',
                'type' => $rule?->type,
                'value' => $rule ? (float) $rule->value : null,
                'amount' => $amount,
            ];
            $paid += $amount;
        }

        return [
            'base' => round($base, 2),
            'lines' => $lines,
            'company' => round(max(0.0, $base - $paid), 2),
        ];
    }

    /**
     * The real split for an actual sale: walks the selling VIP member's upline,
     * pays each role present in the chain its configured share, and folds any
     * absent role (e.g. no Branch Manager linked) into the company remainder.
     *
     * @return array{base: float, lines: array<int, array{role: string, user_id: int, type: string, value: float, amount: float}>, company: float}
     */
    public function calculateSplit(Product $product, User $seller, float $base): array
    {
        $beneficiaries = $this->resolveBeneficiaries($seller);
        $lines = [];
        $paid = 0.0;

        foreach (array_keys(self::EARNING_ROLES) as $role) {
            $user = $beneficiaries[$role] ?? null;
            if (! $user) {
                continue;
            }

            $rule = $this->resolveRule($product, $role);
            $amount = $this->ruleAmount($rule, $base);
            if ($amount <= 0) {
                continue;
            }

            $lines[] = [
                'role' => $role,
                'user_id' => $user->id,
                'type' => $rule->type,
                'value' => (float) $rule->value,
                'amount' => $amount,
            ];
            $paid += $amount;
        }

        return [
            'base' => round($base, 2),
            'lines' => $lines,
            'company' => round(max(0.0, $base - $paid), 2),
        ];
    }

    /**
     * Map each earning role to the actual user in a seller's upline. The seller is
     * the VIP member; walking their `created_by` chain finds whoever holds the
     * Commission Partner / Branch Manager roles — no fixed ordering is assumed.
     *
     * @return array<string, User|null>
     */
    public function resolveBeneficiaries(User $seller): array
    {
        $out = array_fill_keys(array_keys(self::EARNING_ROLES), null);
        // The seller owns the storefront: they are the VIP member beneficiary.
        $out['vip_member'] = $seller;

        $node = $seller->creator;
        $guard = 0;

        while ($node && $guard++ < 12) {
            foreach (array_keys(self::EARNING_ROLES) as $role) {
                if (! $out[$role] && $node->hasRole($role)) {
                    $out[$role] = $node;
                }
            }
            $node = $node->creator;
        }

        return $out;
    }
}
