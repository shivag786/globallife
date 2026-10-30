<?php

namespace App\Services;

use App\Models\BusinessSettlement;
use App\Models\Order;
use App\Models\User;
use App\Models\VipMicrosite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Money a business is owed for its OWN products sold through the company checkout.
 *
 * The company's Razorpay collects online payments, so that money has to be
 * passed on. Cash on delivery is collected by the business at the door and is
 * never owed by the company — it is reported, not settled.
 *
 * An order becomes payable once it is paid online AND delivered, which keeps a
 * cancelled or undelivered order from being paid out. Settling covers every
 * payable order at that moment and stamps them with the settlement id, so the
 * same order can never be paid twice. Separate from both commission systems:
 * these lines earn no upline commission.
 */
class BusinessSalesService
{
    private const CLOSED = ['cancelled', 'refunded'];

    /**
     * @return Builder<Order>
     */
    public function payableOrders(VipMicrosite $microsite): Builder
    {
        return Order::query()
            ->where('fulfilled_by_microsite_id', $microsite->id)
            ->where('payment_method', 'online')
            ->where('payment_status', 'paid')
            ->where('status', 'delivered')
            ->whereNull('business_settlement_id');
    }

    public function payableAmount(VipMicrosite $microsite): float
    {
        return round((float) $this->payableOrders($microsite)->sum('total'), 2);
    }

    /**
     * One row per business that has ever had an order, for the admin report.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function summaries(): Collection
    {
        $closed = "'".implode("','", self::CLOSED)."'";

        $rows = Order::query()
            ->whereNotNull('fulfilled_by_microsite_id')
            ->groupBy('fulfilled_by_microsite_id')
            ->select('fulfilled_by_microsite_id')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw("SUM(CASE WHEN status NOT IN ($closed) THEN total ELSE 0 END) as gross")
            ->selectRaw("SUM(CASE WHEN status NOT IN ($closed) AND payment_method = 'cod' THEN total ELSE 0 END) as cod_total")
            ->selectRaw("SUM(CASE WHEN status NOT IN ($closed) AND payment_method = 'online' AND payment_status = 'paid' THEN total ELSE 0 END) as online_total")
            ->selectRaw("SUM(CASE WHEN payment_method = 'online' AND payment_status = 'paid' AND status = 'delivered' AND business_settlement_id IS NULL THEN total ELSE 0 END) as payable")
            ->selectRaw("SUM(CASE WHEN payment_method = 'online' AND payment_status = 'paid' AND status NOT IN ('delivered', $closed) THEN total ELSE 0 END) as awaiting_delivery")
            ->selectRaw("SUM(CASE WHEN status IN ('confirmed', 'processing', 'dispatched') THEN 1 ELSE 0 END) as open_count")
            ->get()
            ->keyBy('fulfilled_by_microsite_id');

        if ($rows->isEmpty()) {
            return collect();
        }

        $settled = BusinessSettlement::query()
            ->whereIn('vip_microsite_id', $rows->keys())
            ->groupBy('vip_microsite_id')
            ->selectRaw('vip_microsite_id, SUM(amount) as settled, MAX(paid_at) as last_paid_at')
            ->get()
            ->keyBy('vip_microsite_id');

        $microsites = VipMicrosite::with(['city', 'user', 'vipPlan'])->whereIn('id', $rows->keys())->get();

        return $microsites->map(function (VipMicrosite $microsite) use ($rows, $settled) {
            $row = $rows->get($microsite->id);

            return [
                'microsite' => $microsite,
                'orders_count' => (int) $row->orders_count,
                'open_count' => (int) $row->open_count,
                'gross' => round((float) $row->gross, 2),
                'cod_total' => round((float) $row->cod_total, 2),
                'online_total' => round((float) $row->online_total, 2),
                'awaiting_delivery' => round((float) $row->awaiting_delivery, 2),
                'payable' => round((float) $row->payable, 2),
                'settled' => round((float) ($settled->get($microsite->id)?->settled ?? 0), 2),
                'last_paid_at' => $settled->get($microsite->id)?->last_paid_at,
            ];
        })->sortByDesc('payable')->values();
    }

    /**
     * Totals across every business, for the cards above the report.
     *
     * @param  Collection<int, array<string, mixed>>  $summaries
     * @return array<string, float|int>
     */
    public function totals(Collection $summaries): array
    {
        return [
            'gross' => round($summaries->sum('gross'), 2),
            'online_total' => round($summaries->sum('online_total'), 2),
            'cod_total' => round($summaries->sum('cod_total'), 2),
            'payable' => round($summaries->sum('payable'), 2),
            'settled' => round($summaries->sum('settled'), 2),
            'businesses_due' => $summaries->where('payable', '>', 0)->count(),
        ];
    }

    /**
     * Record that the company has transferred a business everything currently
     * payable. The amount is computed here from the locked orders, never typed,
     * so the record always matches exactly the orders it marks settled.
     */
    public function settle(
        VipMicrosite $microsite,
        User $admin,
        ?string $utr = null,
        ?UploadedFile $screenshot = null,
        ?string $note = null,
    ): BusinessSettlement {
        if (blank($utr) && ! $screenshot) {
            throw new RuntimeException('Record a UTR / transaction number or upload a payment screenshot.');
        }

        return DB::transaction(function () use ($microsite, $admin, $utr, $screenshot, $note) {
            $orders = $this->payableOrders($microsite)->lockForUpdate()->get(['id', 'total']);

            if ($orders->isEmpty()) {
                throw new RuntimeException('Nothing is payable to this business right now.');
            }

            $settlement = BusinessSettlement::create([
                'vip_microsite_id' => $microsite->id,
                'amount' => round((float) $orders->sum('total'), 2),
                'orders_count' => $orders->count(),
                'utr_number' => blank($utr) ? null : trim($utr),
                'payment_screenshot_path' => $screenshot?->store('uploads', 'public'),
                'note' => $note,
                'paid_by' => $admin->id,
                'paid_at' => now(),
            ]);

            Order::whereIn('id', $orders->pluck('id'))->update(['business_settlement_id' => $settlement->id]);

            return $settlement;
        });
    }
}
