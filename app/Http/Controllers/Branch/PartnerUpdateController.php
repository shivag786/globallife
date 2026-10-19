<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Services\CommissionPartnerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * A Branch Manager acknowledging Super Admin's notes about their partners.
 */
class PartnerUpdateController extends Controller
{
    public function __construct(private readonly CommissionPartnerService $partners) {}

    /**
     * Mark one note (`id`) or all of this Branch Manager's notes as read. The
     * service only ever touches notes addressed to them.
     */
    public function markRead(Request $request): RedirectResponse
    {
        $data = $request->validate(['id' => ['nullable', 'integer']]);

        $this->partners->markUpdatesRead(Auth::user(), isset($data['id']) ? [(int) $data['id']] : null);

        return back()->with('status', 'Marked as read.');
    }
}
