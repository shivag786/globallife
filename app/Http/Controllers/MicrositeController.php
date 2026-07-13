<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicReviewRequest;
use App\Models\VipMicrosite;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MicrositeController extends Controller
{
    public function show(Request $request, string $citySlug, string $businessSlug, string $secureId): View
    {
        $microsite = $this->resolve($citySlug, $businessSlug, $secureId);

        $microsite->load([
            'banners' => fn ($q) => $q->where('is_visible', true),
            'services' => fn ($q) => $q->where('status', 'published'),
            'products' => fn ($q) => $q->where('status', 'published'),
            'galleryItems' => fn ($q) => $q->where('is_visible', true),
            'videos' => fn ($q) => $q->where('is_visible', true),
            'faqs' => fn ($q) => $q->where('is_visible', true),
            'reviews' => fn ($q) => $q->where('status', 'approved')->latest(),
            'vipPlan', 'city',
        ]);

        $microsite->events()->create(['event_type' => 'page_view']);

        return view('microsite.show', ['microsite' => $microsite]);
    }

    public function storeReview(StorePublicReviewRequest $request, string $citySlug, string $businessSlug, string $secureId): RedirectResponse
    {
        $microsite = $this->resolve($citySlug, $businessSlug, $secureId);

        $microsite->reviews()->create([
            'customer_name' => $request->input('customer_name'),
            'rating' => $request->input('rating'),
            'review_text' => $request->input('review_text'),
            'review_date' => now(),
            'status' => 'pending',
        ]);

        return back()->with('status', 'Thanks for your review! It will appear once approved.');
    }

    private function resolve(string $citySlug, string $businessSlug, string $secureId): VipMicrosite
    {
        if (! preg_match('/^(\d+)-([A-Za-z0-9]+)-(\d+)$/', $secureId, $matches)) {
            abort(404);
        }

        [, $userId, $token, $createdById] = $matches;

        return VipMicrosite::with(['user', 'city', 'vipPlan'])
            ->where('user_id', $userId)
            ->where('secure_token', $token)
            ->where('business_slug', $businessSlug)
            ->where('status', 'active')
            ->whereHas('city', fn ($query) => $query->where('slug', $citySlug))
            ->whereHas('user', fn ($query) => $query->where('created_by', $createdById)->where('status', 'active'))
            ->firstOrFail();
    }
}
