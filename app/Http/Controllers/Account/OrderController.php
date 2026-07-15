<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('account.orders.index', [
            'orders' => Order::where('user_id', Auth::id())->withCount('items')->latest()->paginate(10),
        ]);
    }

    public function show(Order $order): View
    {
        abort_unless($order->user_id === Auth::id(), 403);

        return view('account.orders.show', ['order' => $order->load('items')]);
    }
}
