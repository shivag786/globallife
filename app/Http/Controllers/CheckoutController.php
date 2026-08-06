<?php

namespace App\Http\Controllers;

use App\Mail\OrderPlacedMail;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\StorefrontContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

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

        $user = Auth::user();

        return view('checkout.index', [
            'items' => $this->cart->items(),
            'totals' => $this->cart->totals(),
            'user' => $user,
            'addresses' => $user ? $user->addresses : collect(),
        ]);
    }

    /**
     * New customer: create an account with their chosen password and sign them in.
     */
    public function register(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique' => 'You already have an account with this email. Please use “I have an account” to sign in.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'password' => Hash::make($data['password']),
            'status' => 'active',
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        Role::findOrCreate('customer', 'web');
        $user->assignRole('customer');

        Auth::login($user);
        $request->session()->regenerate();

        return $this->identified($request, 'Account created — you’re signed in.');
    }

    /**
     * Returning customer: authenticate with their existing credentials.
     */
    public function login(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages(['email' => 'This account is not active. Please contact support.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return $this->identified($request, 'Signed in.');
    }

    public function store(Request $request, OrderService $orders): RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $user = Auth::user();
        if (! $user) {
            return redirect()->route('checkout.index')->with('error', 'Please sign in or create an account to place your order.');
        }

        $validated = $request->validate([
            'address_id' => ['required', 'integer'],
            'delivery_notes' => ['nullable', 'string', 'max:1000'],
            'payment_choice' => ['required', 'in:online_success,online_fail,cod'],
        ]);

        $address = Address::where('id', $validated['address_id'])->where('user_id', $user->id)->first();
        if (! $address) {
            return back()->with('error', 'Please choose a valid delivery address.');
        }

        [$method, $outcome] = match ($validated['payment_choice']) {
            'online_success' => ['online', 'success'],
            'online_fail' => ['online', 'fail'],
            default => ['cod', 'success'],
        };

        $order = $orders->placeFromCart([
            'customer_name' => $address->name,
            'customer_phone' => $address->phone,
            'address' => $address->address,
            'city' => $address->city,
            'state' => $address->state,
            'pincode' => $address->pincode,
            'delivery_notes' => $validated['delivery_notes'] ?? null,
            'payment_method' => $method,
            'payment_outcome' => $outcome,
        ], $user);

        if (! $order) {
            return back()->with('error', 'Payment failed — please try again or choose another payment method.');
        }

        try {
            Mail::to($order->customer_email)->send(new OrderPlacedMail($order));
        } catch (\Throwable $e) {
            report($e);
        }

        $request->session()->put('recent_order_id', $order->id);

        return redirect()->route('checkout.confirmation', $order);
    }

    public function confirmation(Order $order, StorefrontContext $storefront): View
    {
        abort_unless(
            (Auth::check() && Auth::id() === $order->user_id) || session('recent_order_id') === $order->id,
            403,
        );

        $order->load('items');
        $store = $storefront->forOrder($order);

        return view('checkout.confirmation', [
            'order' => $order,
            'newAccount' => false,
            'storefront' => $store,
            'continueUrl' => $storefront->continueUrl($store),
        ]);
    }

    /**
     * Response after a successful register/login: JSON for the AJAX identify step
     * (the page reloads into its authenticated state), or a redirect back to checkout.
     */
    private function identified(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return redirect()->route('checkout.index')->with('status', $message);
    }
}
