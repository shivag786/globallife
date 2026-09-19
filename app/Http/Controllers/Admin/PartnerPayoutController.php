<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PartnerPayoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Month-by-month settlement of Commission Partner earnings: pick a month, see
 * every partner's product-sale + VIP-plan commission for it, drill into one
 * partner for the full statement, and mark the month paid.
 */
class PartnerPayoutController extends Controller
{
    public function __construct(private readonly PartnerPayoutService $payouts) {}

    public function index(Request $request): View
    {
        $period = $this->payouts->normalisePeriod($request->query('period'));
        $rows = $this->payouts->partnerRows($period);

        return view('admin.partner-payouts.index', [
            'periods' => $this->payouts->availablePeriods(),
            'period' => $period,
            'periodLabel' => $this->payouts->periodLabel($period),
            'rows' => $rows,
            'summary' => [
                'partners' => $rows->count(),
                'earned' => $rows->sum('earned'),
                'pending' => $rows->sum('product_pending'),
                'paid' => $rows->sum('paid'),
                'payable' => $rows->sum('payable'),
            ],
        ]);
    }

    public function show(Request $request, User $partner): View
    {
        $this->assertPartner($partner);

        $period = $this->payouts->normalisePeriod($request->query('period'));

        return view('admin.partner-payouts.show', $this->payouts->statement($partner, $period) + [
            'periods' => $this->payouts->availablePeriods(),
        ]);
    }

    public function markPaid(Request $request, User $partner): RedirectResponse
    {
        $this->assertPartner($partner);

        $data = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
            'reference' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $payout = $this->payouts->markPaid(
            $partner,
            $data['period'],
            Auth::user(),
            $data['reference'] ?? null,
            $data['note'] ?? null,
        );

        $back = redirect()->route('admin.partner-payouts.show', ['partner' => $partner, 'period' => $data['period']]);

        if (! $payout) {
            return $back->with('error', 'Nothing is payable for that month — it is already settled.');
        }

        return $back->with('status', sprintf(
            'Marked ₹%s paid to %s for %s. Pending commission is untouched.',
            number_format((float) $payout->amount, 2),
            $partner->name,
            $this->payouts->periodLabel($data['period']),
        ));
    }

    private function assertPartner(User $partner): void
    {
        if (! $partner->hasRole('commission_partner')) {
            throw new NotFoundHttpException;
        }
    }
}
