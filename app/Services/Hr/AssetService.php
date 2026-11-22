<?php

namespace App\Services\Hr;

use App\Models\Hr\Asset;
use App\Models\Hr\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Company assets (laptops, phones, ID cards…) and who holds them. Each
 * hand-over is kept in asset_assignments so the history survives returns.
 */
class AssetService
{
    public function __construct(private readonly HrMailService $mail) {}

    public function assign(Asset $asset, Employee $employee, string $date): void
    {
        if ($asset->status !== 'available') {
            throw ValidationException::withMessages(['employee_id' => 'Only an available asset can be assigned. Return it first.']);
        }

        if (! $employee->isWorking()) {
            throw ValidationException::withMessages(['employee_id' => 'Assets can only be assigned to current employees.']);
        }

        DB::transaction(function () use ($asset, $employee, $date) {
            $asset->update(['status' => 'assigned', 'employee_id' => $employee->id, 'assigned_on' => $date]);
            $asset->assignments()->create(['employee_id' => $employee->id, 'assigned_on' => $date]);
        });

        $this->mail->notice($employee->user, 'Company asset assigned to you', [
            "{$asset->name} ({$asset->asset_tag}) has been assigned to you on ".\Carbon\Carbon::parse($date)->format('d M Y').'.',
            'Please take good care of it and return it to HR when asked or when you leave.',
        ]);
    }

    public function returnAsset(Asset $asset, string $date, ?string $note = null, string $newStatus = 'available'): void
    {
        if ($asset->status !== 'assigned') {
            throw ValidationException::withMessages(['asset' => 'This asset is not assigned to anyone.']);
        }

        DB::transaction(function () use ($asset, $date, $note, $newStatus) {
            $asset->assignments()->whereNull('returned_on')->update(['returned_on' => $date, 'return_note' => $note]);
            $asset->update(['status' => $newStatus, 'employee_id' => null, 'assigned_on' => null]);
        });
    }
}
