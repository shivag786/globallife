<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MarkWithdrawalPaidRequest;
use App\Models\WithdrawalRequest;
use App\Services\WithdrawalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * The admin queue of VIP-member withdrawal requests: who asked, for how much,
 * and a Mark as Paid action that records the proof of the manual transfer.
 */
class WithdrawalController extends Controller
{
    public function __construct(private readonly WithdrawalService $withdrawals) {}

    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ['pending', 'paid'], true)
            ? $request->query('status')
            : null;

        $query = WithdrawalRequest::with(['user.wallet', 'payer'])
            // Pending first: this page is a work queue, not an archive.
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        return view('admin.withdrawals.index', [
            'requests' => $query->paginate(25)->withQueryString(),
            'status' => $status,
            'totals' => [
                'pending_count' => WithdrawalRequest::pending()->count(),
                'pending_amount' => (float) WithdrawalRequest::pending()->sum('amount'),
                'paid_count' => WithdrawalRequest::paid()->count(),
                'paid_amount' => (float) WithdrawalRequest::paid()->sum('amount'),
            ],
        ]);
    }

    public function markPaid(MarkWithdrawalPaidRequest $request, WithdrawalRequest $withdrawal): RedirectResponse
    {
        try {
            $this->withdrawals->markPaid(
                $withdrawal,
                Auth::user(),
                $request->input('utr_number'),
                $request->file('payment_screenshot'),
                $request->input('admin_note'),
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', sprintf(
            'Marked ₹%s paid to %s. They can now see the payment details.',
            number_format((float) $withdrawal->amount, 2),
            $withdrawal->user->name,
        ));
    }
}
