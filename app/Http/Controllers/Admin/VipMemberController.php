<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VipMicrosite;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class VipMemberController extends Controller
{
    /**
     * Super Admin oversight of every VIP member: who they are, their plan and
     * renewal date, their upline (Commission Partner → Branch Manager) with the
     * commission each earned on activation, and a link to their live microsite.
     */
    public function index(Request $request): View
    {
        $query = VipMicrosite::with([
            'user', 'city', 'vipPlan',
            'commissionTransaction.commissionPartner',
            'commissionTransaction.branchManager',
            // Fallback upline for members activated outside the commission flow.
            'user.creator.creator',
        ])->latest('id');

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        return view('admin.vip-members.index', [
            'members' => $query->paginate(20)->withQueryString(),
            'search' => $search,
        ]);
    }
}
