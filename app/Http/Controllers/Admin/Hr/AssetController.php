<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Asset;
use App\Models\Hr\Employee;
use App\Services\Hr\AssetService;
use App\Support\HrAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    public function __construct(private readonly AssetService $assets) {}

    public function index(Request $request): View
    {
        HrAccess::module('assets');

        return view('admin.hr.assets.index', [
            'assets' => Asset::with(['employee.user', 'assignments.employee.user'])
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
                ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
                ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                    ->where('name', 'like', '%'.$request->input('q').'%')
                    ->orWhere('asset_tag', 'like', '%'.$request->input('q').'%')
                    ->orWhere('serial_number', 'like', '%'.$request->input('q').'%')))
                ->orderBy('asset_tag')
                ->paginate(25)
                ->withQueryString(),
            'employees' => Employee::working()->with('user')->orderBy('employee_code')->get(),
            'canCreate' => Auth::user()->can('assets.create'),
            'canEdit' => Auth::user()->can('assets.edit'),
            'canDelete' => Auth::user()->can('assets.delete'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        HrAccess::ability('assets.create');

        Asset::create([...$this->validated($request), 'status' => 'available']);

        return back()->with('status', 'Asset added.');
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        HrAccess::ability('assets.edit');

        $data = $this->validated($request, $asset);

        // Assigned is set only by assign()/return, never by editing.
        $status = $request->input('status');
        if ($asset->status !== 'assigned' && in_array($status, ['available', 'repair', 'retired'], true)) {
            $data['status'] = $status;
        }

        $asset->update($data);

        return back()->with('status', 'Asset updated.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        HrAccess::ability('assets.delete');

        if ($asset->status === 'assigned') {
            return back()->with('error', 'Take the asset back from the employee before deleting it.');
        }

        $asset->delete();

        return back()->with('status', 'Asset deleted.');
    }

    public function assign(Request $request, Asset $asset): RedirectResponse
    {
        HrAccess::ability('assets.edit');

        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'assigned_on' => ['required', 'date', 'before_or_equal:'.\App\Support\HrClock::today()->toDateString()],
        ]);

        $employee = Employee::findOrFail($data['employee_id']);
        $this->assets->assign($asset, $employee, $data['assigned_on']);

        return back()->with('status', "{$asset->name} assigned to {$employee->name}.");
    }

    public function returnAsset(Request $request, Asset $asset): RedirectResponse
    {
        HrAccess::ability('assets.edit');

        $data = $request->validate([
            'returned_on' => ['required', 'date', 'before_or_equal:'.\App\Support\HrClock::today()->toDateString()],
            'return_note' => ['nullable', 'string', 'max:255'],
            'new_status' => ['required', Rule::in(['available', 'repair', 'retired'])],
        ]);

        $this->assets->returnAsset($asset, $data['returned_on'], $data['return_note'] ?? null, $data['new_status']);

        return back()->with('status', "{$asset->name} marked as returned.");
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Asset $asset = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'asset_tag' => ['required', 'string', 'max:40', Rule::unique('assets', 'asset_tag')->ignore($asset?->id)],
            'category' => ['required', Rule::in(Asset::CATEGORIES)],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
