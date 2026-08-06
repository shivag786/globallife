<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Lightweight poll for the admin new-order sound alert. Returns the newest
     * order id and how many orders have arrived since the caller's last-seen id.
     */
    public function poll(Request $request): JsonResponse
    {
        $since = (int) $request->query('since', 0);
        $latestId = (int) Order::max('id');

        return response()->json([
            'latest_id' => $latestId,
            'new' => $since > 0 ? Order::where('id', '>', $since)->count() : 0,
        ]);
    }

    public function index(Request $request): View
    {
        $query = Order::withCount('items')->latest('placed_at');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return view('admin.orders.index', [
            'orders' => $query->paginate(20)->withQueryString(),
            'activeStatus' => $request->query('status'),
            'statuses' => ['pending', 'confirmed', 'processing', 'dispatched', 'delivered', 'cancelled', 'refunded'],
        ]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load(['items.seller', 'earnings.beneficiary', 'user']),
        ]);
    }

    public function updateStatus(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,confirmed,processing,dispatched,delivered,cancelled,refunded'],
        ]);

        $orders->updateStatus($order, $data['status']);

        $note = $data['status'] === 'delivered'
            ? 'Order marked delivered — commission approved and credited to wallets.'
            : 'Order status updated.';

        return back()->with('status', $note);
    }

    /**
     * Admin override of an order's estimated delivery date.
     */
    public function updateDelivery(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'expected_delivery_date' => ['required', 'date'],
        ]);

        $order->update(['expected_delivery_date' => $data['expected_delivery_date']]);

        return back()->with('status', 'Expected delivery date updated.');
    }
}
