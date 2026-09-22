<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * VIP members withdrawing their product-commission wallet balance.
 *
 * No payment gateway is involved: the member asks, an admin transfers the money
 * by hand and records the UTR and/or a screenshot as proof. The wallet is
 * debited at that point — not when the request is made — so a pending request
 * never makes money disappear from the member's view.
 *
 * Kept separate from `PartnerPayoutService`, which is the Super Admin settling a
 * Commission Partner's month. Same wallet, two different mechanisms.
 */
class WithdrawalService
{
    /** A request must be worth at least this much. */
    public const MINIMUM_AMOUNT = 500;

    /** Only one request per this many hours. */
    public const COOLDOWN_HOURS = 24;

    /**
     * Balance the member may actually ask for: their wallet minus anything
     * already requested and not yet paid, so two requests can never together
     * exceed the wallet.
     */
    public function availableBalance(User $member): float
    {
        $balance = (float) ($member->wallet?->balance ?? 0);
        $awaiting = (float) WithdrawalRequest::where('user_id', $member->id)->pending()->sum('amount');

        return round(max(0, $balance - $awaiting), 2);
    }

    /**
     * The most recent request, whatever its state — drives the cooldown.
     */
    public function latestRequest(User $member): ?WithdrawalRequest
    {
        return WithdrawalRequest::where('user_id', $member->id)->latest('created_at')->first();
    }

    /**
     * Why this member cannot request right now, or null if they can.
     *
     * The cooldown is checked before the amount so that pressing an enabled
     * button always explains itself, rather than complaining about the figure.
     */
    public function blockReason(User $member): ?string
    {
        $latest = $this->latestRequest($member);

        if ($latest && $latest->created_at->diffInHours(now()) < self::COOLDOWN_HOURS) {
            return 'Withdrawal request is already available, kindly try after 24 hours.';
        }

        if (WithdrawalRequest::where('user_id', $member->id)->pending()->exists()) {
            return 'Withdrawal request is already available — it is awaiting payment from the admin.';
        }

        if ($this->availableBalance($member) < self::MINIMUM_AMOUNT) {
            return sprintf(
                'You need at least ₹%s available to request a withdrawal.',
                number_format(self::MINIMUM_AMOUNT, 2),
            );
        }

        return null;
    }

    public function canRequest(User $member): bool
    {
        return $this->blockReason($member) === null;
    }

    /**
     * Whether a request is already in flight — pending, or made inside the
     * cooldown. This is what keeps the form (and its enabled button) on screen
     * so that pressing it reports the 24-hour rule, as opposed to having too
     * little balance, where there is genuinely nothing to request.
     */
    public function hasOpenRequest(User $member): bool
    {
        $latest = $this->latestRequest($member);

        if ($latest && $latest->created_at->diffInHours(now()) < self::COOLDOWN_HOURS) {
            return true;
        }

        return WithdrawalRequest::where('user_id', $member->id)->pending()->exists();
    }

    /**
     * Record a request. Nothing leaves the wallet yet.
     *
     * @throws RuntimeException when blocked or the amount is out of range
     */
    public function request(User $member, float $amount): WithdrawalRequest
    {
        return DB::transaction(function () use ($member, $amount) {
            // Lock the wallet so two submits cannot both pass the checks below.
            $wallet = Wallet::lockForUpdate()->firstOrCreate(['user_id' => $member->id]);

            if ($reason = $this->blockReason($member)) {
                throw new RuntimeException($reason);
            }

            $amount = round($amount, 2);

            if ($amount < self::MINIMUM_AMOUNT) {
                throw new RuntimeException(sprintf(
                    'The minimum withdrawal is ₹%s.',
                    number_format(self::MINIMUM_AMOUNT, 2),
                ));
            }

            $available = $this->availableBalance($member);

            if ($amount > $available) {
                throw new RuntimeException(sprintf(
                    'You can request at most ₹%s right now.',
                    number_format($available, 2),
                ));
            }

            return WithdrawalRequest::create([
                'user_id' => $member->id,
                'amount' => $amount,
                'status' => 'pending',
                'wallet_balance_at_request' => (float) $wallet->balance,
            ]);
        });
    }

    /**
     * Mark a request paid: store the proof and take the money off the wallet.
     *
     * At least one of `$utr` / `$screenshot` must be present — that is what the
     * member is shown as evidence, so a payment cannot be recorded without it.
     *
     * @throws RuntimeException when already paid or no proof was given
     */
    public function markPaid(
        WithdrawalRequest $request,
        User $admin,
        ?string $utr = null,
        ?UploadedFile $screenshot = null,
        ?string $note = null,
    ): WithdrawalRequest {
        return DB::transaction(function () use ($request, $admin, $utr, $screenshot, $note) {
            /** @var WithdrawalRequest $request */
            $request = WithdrawalRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();

            if ($request->isPaid()) {
                throw new RuntimeException('This withdrawal has already been marked paid.');
            }

            if (blank($utr) && ! $screenshot) {
                throw new RuntimeException('Record a UTR number or upload a payment screenshot.');
            }

            $wallet = Wallet::lockForUpdate()->firstOrCreate(['user_id' => $request->user_id]);

            // Never drive a wallet negative, even if earnings were settled by
            // some other route between the request and the transfer.
            $debit = round(min((float) $wallet->balance, (float) $request->amount), 2);

            if ($debit > 0) {
                $wallet->decrement('balance', $debit);
            }

            $request->update([
                'status' => 'paid',
                'utr_number' => blank($utr) ? null : trim($utr),
                'payment_screenshot_path' => $screenshot
                    ? $screenshot->store('uploads', 'public')
                    : $request->payment_screenshot_path,
                'admin_note' => $note,
                'paid_by' => $admin->id,
                'paid_at' => now(),
            ]);

            return $request->refresh();
        });
    }
}
