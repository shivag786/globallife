<?php

namespace App\Http\Controllers;

use App\Services\WithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * A VIP member asking to withdraw their wallet balance. The button on the wallet
 * page is always enabled, so every refusal is explained here rather than by a
 * disabled control the member cannot interrogate.
 */
class WithdrawalController extends Controller
{
    public function __construct(private readonly WithdrawalService $withdrawals) {}

    public function store(Request $request): RedirectResponse
    {
        $member = Auth::user();

        // Checked before validating the amount: pressing the button while a
        // request is already open must report that, not a problem with the figure.
        if ($reason = $this->withdrawals->blockReason($member)) {
            return back()->with('error', $reason);
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:'.WithdrawalService::MINIMUM_AMOUNT],
        ], [
            'amount.min' => 'The minimum withdrawal is ₹'.number_format(WithdrawalService::MINIMUM_AMOUNT, 2).'.',
        ]);

        try {
            $withdrawal = $this->withdrawals->request($member, (float) $data['amount']);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', sprintf(
            'Withdrawal request for ₹%s placed. You will see the payment details here once the admin pays it.',
            number_format((float) $withdrawal->amount, 2),
        ));
    }
}
