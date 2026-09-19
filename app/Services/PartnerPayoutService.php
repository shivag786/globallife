<?php

namespace App\Services;

use App\Models\CommissionEarning;
use App\Models\CommissionPayout;
use App\Models\CommissionTransaction;
use App\Models\User;
use App\Models\Wallet;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Monthly settlement of a Commission Partner's earnings.
 *
 * It reads from BOTH commission systems without merging them:
 *   - product-sale commission  -> `commission_earnings` (pending until delivery;
 *     the approved amounts are what sits in the partner's wallet), and
 *   - VIP-activation commission -> `commission_transactions` (a ledger that never
 *     touched the wallet).
 *
 * A month is bucketed by when the earning was created (order date) / when the
 * microsite was activated, so every row belongs to exactly one month. Paying a
 * month out settles only what is payable *right now*; anything still pending is
 * left alone and becomes payable in that same month once the order is delivered.
 */
class PartnerPayoutService
{
    public const PERIOD_FORMAT = 'Y-m';

    /**
     * Every month that has activity, newest first, as ['2026-09' => 'September 2026'].
     * The current month is always offered even when nothing has happened yet.
     *
     * @return array<string, string>
     */
    public function availablePeriods(): array
    {
        $earliest = collect([
            CommissionEarning::min('created_at'),
            CommissionTransaction::min('activated_at'),
            CommissionPayout::min('paid_at'),
        ])->filter()->map(fn ($date) => CarbonImmutable::parse($date))->min();

        $cursor = ($earliest ?? CarbonImmutable::now())->startOfMonth();
        $now = CarbonImmutable::now()->startOfMonth();

        $periods = [];
        while ($cursor <= $now) {
            $periods[$cursor->format(self::PERIOD_FORMAT)] = $cursor->format('F Y');
            $cursor = $cursor->addMonth();
        }

        return array_reverse($periods, true);
    }

    /**
     * Coerce user input to a usable YYYY-MM, falling back to the current month.
     */
    public function normalisePeriod(?string $period): string
    {
        if (is_string($period) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period) === 1) {
            return $period;
        }

        return CarbonImmutable::now()->format(self::PERIOD_FORMAT);
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public function periodBounds(string $period): array
    {
        $start = CarbonImmutable::createFromFormat(self::PERIOD_FORMAT, $period)->startOfMonth()->startOfDay();

        return [$start, $start->endOfMonth()];
    }

    public function periodLabel(string $period): string
    {
        return $this->periodBounds($period)[0]->format('F Y');
    }

    /**
     * One row per Commission Partner for the month — the list page.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function partnerRows(string $period): Collection
    {
        $partners = User::role('commission_partner')->with('creator')->orderBy('name')->get();

        if ($partners->isEmpty()) {
            return collect();
        }

        $ids = $partners->pluck('id')->all();
        [$from, $to] = $this->periodBounds($period);

        $product = CommissionEarning::whereIn('beneficiary_id', $ids)
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('beneficiary_id')
            ->selectRaw(
                "beneficiary_id,
                 COALESCE(SUM(CASE WHEN status = 'approved' THEN amount END), 0) as approved,
                 COALESCE(SUM(CASE WHEN status = 'pending' THEN amount END), 0) as pending,
                 COUNT(*) as entries"
            )
            ->get()->keyBy('beneficiary_id');

        $vip = CommissionTransaction::whereIn('commission_partner_id', $ids)
            ->whereBetween('activated_at', [$from, $to])
            ->groupBy('commission_partner_id')
            ->selectRaw('commission_partner_id, COALESCE(SUM(commission_partner_amount), 0) as amount, COUNT(*) as activations')
            ->get()->keyBy('commission_partner_id');

        $paid = $this->paidTotals($ids, $period);

        return $partners->map(function (User $partner) use ($product, $vip, $paid) {
            $row = $product->get($partner->id);
            $vipRow = $vip->get($partner->id);
            $paidRow = $paid->get($partner->id);

            return $this->composeTotals(
                partner: $partner,
                productApproved: (float) ($row->approved ?? 0),
                productPending: (float) ($row->pending ?? 0),
                vipAmount: (float) ($vipRow->amount ?? 0),
                paidProduct: (float) ($paidRow->product_amount ?? 0),
                paidVip: (float) ($paidRow->vip_amount ?? 0),
                entries: (int) ($row->entries ?? 0),
                activations: (int) ($vipRow->activations ?? 0),
            );
        });
    }

    /**
     * Full statement for one partner in one month — the detail page.
     *
     * @return array<string, mixed>
     */
    public function statement(User $partner, string $period): array
    {
        [$from, $to] = $this->periodBounds($period);

        $earnings = CommissionEarning::with(['order', 'product', 'sellerMicrosite'])
            ->where('beneficiary_id', $partner->id)
            ->whereBetween('created_at', [$from, $to])
            ->latest('created_at')
            ->get();

        $activations = CommissionTransaction::with(['vipMicrosite.city', 'vipPlan'])
            ->where('commission_partner_id', $partner->id)
            ->whereBetween('activated_at', [$from, $to])
            ->latest('activated_at')
            ->get();

        $paidRow = $this->paidTotals([$partner->id], $period)->get($partner->id);

        return [
            'partner' => $partner,
            'period' => $period,
            'label' => $this->periodLabel($period),
            'earnings' => $earnings,
            'activations' => $activations,
            'payouts' => CommissionPayout::with('payer')
                ->where('user_id', $partner->id)
                ->where('period', $period)
                ->latest('paid_at')
                ->get(),
            'totals' => $this->composeTotals(
                partner: $partner,
                productApproved: (float) $earnings->where('status', 'approved')->sum('amount'),
                productPending: (float) $earnings->where('status', 'pending')->sum('amount'),
                vipAmount: (float) $activations->sum('commission_partner_amount'),
                paidProduct: (float) ($paidRow->product_amount ?? 0),
                paidVip: (float) ($paidRow->vip_amount ?? 0),
                entries: $earnings->count(),
                activations: $activations->count(),
            ),
        ];
    }

    /**
     * Settle everything payable for the month: writes the payout row and takes the
     * product-commission share off the wallet, so the partner's withdrawable
     * balance drops to zero while pending earnings stay untouched.
     *
     * Returns null when there is nothing left to pay for that month.
     */
    public function markPaid(User $partner, string $period, User $actor, ?string $reference = null, ?string $note = null): ?CommissionPayout
    {
        return DB::transaction(function () use ($partner, $period, $actor, $reference, $note) {
            // Lock the wallet row first so two admins cannot settle the same month twice.
            $wallet = Wallet::lockForUpdate()->firstOrCreate(['user_id' => $partner->id]);

            $totals = $this->statement($partner, $period)['totals'];

            if ($totals['payable'] <= 0) {
                return null;
            }

            $walletDebit = round(min((float) $wallet->balance, $totals['product_payable']), 2);

            if ($walletDebit > 0) {
                $wallet->decrement('balance', $walletDebit);
            }

            return CommissionPayout::create([
                'user_id' => $partner->id,
                'period' => $period,
                'product_amount' => $totals['product_payable'],
                'vip_amount' => $totals['vip_payable'],
                'amount' => $totals['payable'],
                'wallet_debited' => $walletDebit,
                'reference' => $reference,
                'note' => $note,
                'paid_by' => $actor->id,
                'paid_at' => now(),
            ]);
        });
    }

    /**
     * Already-settled amounts per user for a month.
     *
     * @param  array<int, int>  $userIds
     * @return Collection<int, object>
     */
    private function paidTotals(array $userIds, string $period): Collection
    {
        return CommissionPayout::whereIn('user_id', $userIds)
            ->where('period', $period)
            ->groupBy('user_id')
            ->selectRaw('user_id, COALESCE(SUM(product_amount), 0) as product_amount, COALESCE(SUM(vip_amount), 0) as vip_amount')
            ->get()->keyBy('user_id');
    }

    /**
     * The one place a month's numbers are derived, so the list and the detail page
     * can never disagree.
     *
     * @return array<string, mixed>
     */
    private function composeTotals(
        User $partner,
        float $productApproved,
        float $productPending,
        float $vipAmount,
        float $paidProduct,
        float $paidVip,
        int $entries,
        int $activations,
    ): array {
        $productPayable = round(max(0, $productApproved - $paidProduct), 2);
        $vipPayable = round(max(0, $vipAmount - $paidVip), 2);
        $earned = round($productApproved + $vipAmount, 2);
        $paid = round($paidProduct + $paidVip, 2);
        $payable = round($productPayable + $vipPayable, 2);

        return [
            'partner' => $partner,
            'product_approved' => round($productApproved, 2),
            'product_pending' => round($productPending, 2),
            'vip_amount' => round($vipAmount, 2),
            'earned' => $earned,
            'paid' => $paid,
            'product_payable' => $productPayable,
            'vip_payable' => $vipPayable,
            'payable' => $payable,
            'entries' => $entries,
            'activations' => $activations,
            'status' => $this->status($earned, $payable, $paid, $productPending),
        ];
    }

    /**
     * Settlement status for the month, in plain words for the badge.
     */
    private function status(float $earned, float $payable, float $paid, float $pending): string
    {
        if ($earned <= 0 && $pending <= 0) {
            return 'no_activity';
        }

        if ($payable > 0) {
            return $paid > 0 ? 'part_paid' : 'unpaid';
        }

        if ($pending > 0) {
            return $paid > 0 ? 'paid_pending_remains' : 'awaiting_delivery';
        }

        return $paid > 0 ? 'paid' : 'no_activity';
    }
}
