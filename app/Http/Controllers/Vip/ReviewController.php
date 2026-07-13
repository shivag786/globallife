<?php

namespace App\Http\Controllers\Vip;

use App\Http\Controllers\Controller;
use App\Models\BusinessReview;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index(): View
    {
        $reviews = Auth::user()->vipMicrosite->reviews()->latest()->get();

        return view('vip.reviews.index', ['reviews' => $reviews]);
    }

    public function approve(BusinessReview $review): RedirectResponse
    {
        abort_unless($review->vip_microsite_id === Auth::user()->vipMicrosite->id, 403);

        $review->update(['status' => 'approved']);

        return back()->with('status', 'Review approved.');
    }

    public function reject(BusinessReview $review): RedirectResponse
    {
        abort_unless($review->vip_microsite_id === Auth::user()->vipMicrosite->id, 403);

        $review->update(['status' => 'rejected']);

        return back()->with('status', 'Review rejected.');
    }
}
