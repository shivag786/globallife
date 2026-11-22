<?php

namespace App\Services\Hr;

use App\Models\Hr\Employee;
use App\Models\Hr\ExpenseClaim;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

/**
 * Expense reimbursement: pending → approved / rejected → paid.
 * Receipts are stored on the private HR disk.
 */
class ExpenseService
{
    public function __construct(private readonly HrMailService $mail) {}

    /**
     * @param  array{title: string, category: string, amount: float|string, expense_date: string, description?: ?string}  $data
     */
    public function submit(Employee $employee, array $data, ?UploadedFile $receipt): ExpenseClaim
    {
        $claim = $employee->expenseClaims()->create([
            'title' => $data['title'],
            'category' => $data['category'],
            'amount' => $data['amount'],
            'expense_date' => $data['expense_date'],
            'description' => $data['description'] ?? null,
            'status' => 'pending',
        ]);

        if ($receipt) {
            $claim->update(['receipt_path' => $receipt->store("hr/{$employee->id}/receipts", EmployeeService::DISK)]);
        }

        return $claim;
    }

    public function approve(ExpenseClaim $claim, User $actor, ?string $note = null): void
    {
        $this->decide($claim, 'approved', $actor, $note);
    }

    public function reject(ExpenseClaim $claim, User $actor, ?string $note = null): void
    {
        $this->decide($claim, 'rejected', $actor, $note);
    }

    public function markPaid(ExpenseClaim $claim): void
    {
        if ($claim->status !== 'approved') {
            throw ValidationException::withMessages(['status' => 'Only an approved claim can be marked as paid.']);
        }

        $claim->update(['status' => 'paid', 'paid_at' => \App\Support\HrClock::now()]);

        $this->mail->notice($claim->employee->user, 'Expense reimbursed', [
            "Your expense claim \"{$claim->title}\" of Rs. ".number_format($claim->amount, 2).' has been paid.',
        ], 'View my claims', route('employee.expenses.index'));
    }

    private function decide(ExpenseClaim $claim, string $status, User $actor, ?string $note): void
    {
        if ($claim->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'This claim has already been reviewed.']);
        }

        $claim->update(['status' => $status, 'reviewed_by' => $actor->id, 'reviewed_at' => \App\Support\HrClock::now(), 'review_note' => $note]);

        $this->mail->notice($claim->employee->user, 'Expense claim '.$status, array_values(array_filter([
            "Your expense claim \"{$claim->title}\" of Rs. ".number_format($claim->amount, 2)." has been {$status}.",
            $note ? 'Note: '.$note : null,
        ])), 'View my claims', route('employee.expenses.index'));
    }
}
