<?php

namespace App\Http\Controllers\Vip;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\BusinessOrderService;
use App\Services\BusinessSalesService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Orders for the business's own products, which it packs and delivers itself.
 */
class OrderController extends Controller
{
    public function __construct(
        private readonly BusinessOrderService $orders,
        private readonly BusinessSalesService $sales,
    ) {
    }

    public function index(Request $request): View
    {
        $microsite = Auth::user()->vipMicrosite;
        $filter = $request->query('status');

        $orders = $microsite->fulfilledOrders()
            ->with('items')
            ->when($filter === 'open', fn ($q) => $q->whereIn('status', ['pending', 'confirmed', 'processing', 'dispatched']))
            ->when($filter === 'delivered', fn ($q) => $q->where('status', 'delivered'))
            ->latest('placed_at')
            ->paginate(20)
            ->withQueryString();

        return view('vip.orders.index', [
            'microsite' => $microsite,
            'orders' => $orders,
            'filter' => $filter,
            'openCount' => $microsite->fulfilledOrders()->whereIn('status', ['confirmed', 'processing', 'dispatched'])->count(),
            'payable' => $this->sales->payableAmount($microsite),
            'settlements' => $microsite->settlements()->latest('paid_at')->get(),
            'sellsDirect' => $microsite->sellsDirect(),
        ]);
    }

    public function show(Order $order): View
    {
        $microsite = Auth::user()->vipMicrosite;
        abort_unless($this->orders->owns($microsite, $order), 404);

        return view('vip.orders.show', [
            'order' => $order->load(['items', 'settlement']),
            'nextStatuses' => $this->orders->nextStatuses($order),
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $microsite = Auth::user()->vipMicrosite;
        abort_unless($this->orders->owns($microsite, $order), 404);

        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(BusinessOrderService::BUSINESS_STATUSES))],
        ]);

        try {
            $this->orders->updateStatus($microsite, $order, $data['status']);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Order marked “'.BusinessOrderService::BUSINESS_STATUSES[$data['status']].'”.');
    }
}
