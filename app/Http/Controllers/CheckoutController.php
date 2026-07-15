<?php

namespace App\Http\Controllers;

use App\Mail\OrderPlacedMail;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function index(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index');
        }

        return view('checkout.index', [
            'items' => $this->cart->items(),
            'totals' => $this->cart->totals(),
            'prefill' => Auth::user(),
        ]);
    }

    public function store(Request $request, OrderService $orders): RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'max:12'],
            'delivery_notes' => ['nullable', 'string', 'max:1000'],
            'payment_choice' => ['required', 'in:online_success,online_fail,cod'],
        ]);

        [$method, $outcome] = match ($validated['payment_choice']) {
            'online_success' => ['online', 'success'],
            'online_fail' => ['online', 'fail'],
            default => ['cod', 'success'],
        };
        $validated['payment_method'] = $method;
        $validated['payment_outcome'] = $outcome;

        $order = $orders->placeFromCart($validated, Auth::user());

        if (! $order) {
            return back()->withInput()->with('error', 'Payment failed — please try again or choose another payment method.');
        }

        // Welcome/order email (with login credentials for new accounts). Never let a
        // mail-transport error break a successful order.
        try {
            Mail::to($order->customer_email)->send(new OrderPlacedMail($order, $orders->newAccount ? $orders->generatedPassword : null));
        } catch (\Throwable $e) {
            report($e);
        }

        // Log a brand-new customer straight into their account.
        if ($orders->newAccount && $order->user_id && ! Auth::check()) {
            Auth::loginUsingId($order->user_id);
        }

        $request->session()->put('recent_order_id', $order->id);
        if ($orders->newAccount) {
            $request->session()->flash('recent_order_new_account', true);
        }

        return redirect()->route('checkout.confirmation', $order);
    }

    public function confirmation(Order $order): View
    {
        abort_unless(
            (Auth::check() && Auth::id() === $order->user_id) || session('recent_order_id') === $order->id,
            403,
        );

        return view('checkout.confirmation', [
            'order' => $order->load('items'),
            'newAccount' => (bool) session('recent_order_new_account'),
        ]);
    }
}
