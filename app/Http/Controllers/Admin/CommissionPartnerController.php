<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;

class CommissionPartnerController extends Controller
{
    /**
     * Read-only oversight of every Commission Partner across all Branch Managers.
     * Commission Partners are created and managed by their Branch Manager, not here.
     */
    public function index(): View
    {
        $partners = User::role('commission_partner')
            ->with(['cities', 'creator'])
            ->orderBy('name')
            ->get();

        return view('admin.commission-partners.index', ['partners' => $partners]);
    }
}
