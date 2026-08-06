<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index(): View
    {
        return view('account.addresses.index', [
            'addresses' => Auth::user()->addresses,
            'user' => Auth::user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $user = Auth::user();
        // First address becomes the default automatically; an explicit "make default"
        // demotes the others.
        $makeDefault = $request->boolean('is_default') || $user->addresses()->count() === 0;

        DB::transaction(function () use ($user, $data, $makeDefault) {
            if ($makeDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            $user->addresses()->create($data + ['is_default' => $makeDefault]);
        });

        return $this->redirectBack($request, 'Address saved.');
    }

    public function setDefault(Request $request, Address $address): RedirectResponse
    {
        $this->authorizeAddress($address);

        DB::transaction(function () use ($address) {
            $address->user->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return $this->redirectBack($request, 'Default address updated.');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        $this->authorizeAddress($address);

        $wasDefault = $address->is_default;
        $address->delete();

        // Promote another address to default so the account always has one.
        if ($wasDefault && ($next = Auth::user()->addresses()->first())) {
            $next->update(['is_default' => true]);
        }

        return $this->redirectBack($request, 'Address removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'max:12'],
        ]);
    }

    private function authorizeAddress(Address $address): void
    {
        abort_unless($address->user_id === Auth::id(), 403);
    }

    private function redirectBack(Request $request, string $message): RedirectResponse
    {
        if ($request->input('redirect_to') === 'checkout') {
            return redirect()->route('checkout.index')->with('status', $message);
        }

        return redirect()->route('account.addresses.index')->with('status', $message);
    }
}
