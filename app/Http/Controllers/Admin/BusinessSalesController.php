<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettleBusinessRequest;
use App\Models\VipMicrosite;
use App\Services\BusinessSalesService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Businesses selling their own products through the company checkout: what
 * they sold, what the company collected for them online, and paying it over.
 */
class BusinessSalesController extends Controller
{
    public function __construct(private readonly BusinessSalesService $sales)
    {
    }

    public function index(): View
    {
        $summaries = $this->sales->summaries();

        return view('admin.business-sales.index', [
            'summaries' => $summaries,
            'totals' => $this->sales->totals($summaries),
        ]);
    }

    public function show(Request $request, VipMicrosite $microsite): View
    {
        $filter = $request->query('filter');

        $orders = $microsite->fulfilledOrders()
            ->with(['items', 'settlement'])
            ->when($filter === 'payable', fn ($q) => $q->whereKey($this->sales->payableOrders($microsite)->pluck('id')))
            ->when($filter === 'cod', fn ($q) => $q->where('payment_method', 'cod'))
            ->latest('placed_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.business-sales.show', [
            'microsite' => $microsite->load(['city', 'user', 'vipPlan']),
            'orders' => $orders,
            'filter' => $filter,
            'payable' => $this->sales->payableAmount($microsite),
            'payableCount' => $this->sales->payableOrders($microsite)->count(),
            'settlements' => $microsite->settlements()->with('payer')->latest('paid_at')->get(),
        ]);
    }

    public function settle(SettleBusinessRequest $request, VipMicrosite $microsite): RedirectResponse
    {
        try {
            $settlement = $this->sales->settle(
                $microsite,
                $request->user(),
                $request->validated('utr_number'),
                $request->file('payment_screenshot'),
                $request->validated('note'),
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', sprintf(
            '₹%s transfer to %s recorded against %d order%s.',
            number_format((float) $settlement->amount, 2),
            $microsite->business_name,
            $settlement->orders_count,
            $settlement->orders_count === 1 ? '' : 's',
        ));
    }
}
