<?php

namespace App\Http\Controllers;

use App\Models\Hr\Employee;
use App\Models\Hr\EmployeeDocument;
use App\Models\Hr\ExpenseClaim;
use App\Services\Hr\EmployeeService;
use App\Services\PermissionMatrixService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Private HR files (photo, documents, expense receipts). Visible to the
 * employee they belong to and to HR with the matching module.
 */
class HrFileController extends Controller
{
    public function photo(Employee $employee): StreamedResponse
    {
        $this->authorizeFor($employee, 'employees');

        return $this->serve($employee->photo_path);
    }

    public function document(EmployeeDocument $document): StreamedResponse
    {
        $this->authorizeFor($document->employee, 'employees');

        return $this->serve($document->path, $document->original_name);
    }

    public function receipt(ExpenseClaim $claim): StreamedResponse
    {
        $this->authorizeFor($claim->employee, 'expenses');

        return $this->serve($claim->receipt_path);
    }

    private function authorizeFor(Employee $employee, string $module): void
    {
        $user = Auth::user();

        abort_unless(
            $user->employee?->id === $employee->id || PermissionMatrixService::userCanAccessModule($user, $module),
            403,
        );
    }

    private function serve(?string $path, ?string $downloadName = null): StreamedResponse
    {
        $disk = Storage::disk(EmployeeService::DISK);

        abort_unless($path && $disk->exists($path), 404);

        return $disk->response($path, $downloadName, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
