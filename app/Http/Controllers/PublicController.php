<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\VipMicrosite;
use App\Repositories\BlogPostRepository;
use App\Repositories\HomeSectionRepository;
use App\Repositories\VipPlanRepository;
use Illuminate\Contracts\View\View;

class PublicController extends Controller
{
    /**
     * The default homepage — CMS-driven HomeSection content (editable from admin).
     */
    public function home(HomeSectionRepository $sections): View
    {
        return view('welcome', ['sections' => $sections->activeOrdered()]);
    }

    /**
     * The premium flagship "experience" design at /experience — a standalone page
     * built on real products, plans, and a live microsite.
     */
    public function experience(VipPlanRepository $plans): View
    {
        $products = Product::where('status', 'active')
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->limit(4)
            ->get();

        $heroMicrosite = VipMicrosite::with(['city', 'user'])
            ->whereHas('city')
            ->whereHas('user')
            ->latest('id')
            ->first();

        return view('experience', [
            'products' => $products,
            'plans' => $plans->activeOrdered(),
            'heroMicrosite' => $heroMicrosite,
        ]);
    }

    public function vipPlans(VipPlanRepository $plans): View
    {
        return view('vip-plans.index', ['plans' => $plans->activeOrdered()]);
    }

    /**
     * "Third" — a premium wellness-brand + digital-business-ecosystem landing page,
     * built on real products, plans, a live microsite, and recent blog posts.
     */
    public function third(VipPlanRepository $plans, BlogPostRepository $posts): View
    {
        $products = Product::where('status', 'active')
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->limit(8)
            ->get();

        $heroMicrosite = VipMicrosite::with(['city', 'user'])
            ->whereHas('city')
            ->whereHas('user')
            ->latest('id')
            ->first();

        return view('third', [
            'products' => $products,
            'plans' => $plans->activeOrdered(),
            'heroMicrosite' => $heroMicrosite,
            'posts' => $posts->latestPublished(3),
        ]);
    }

    /**
     * "Fourth" — a premium Business Growth Platform landing page for business owners,
     * built on real products and a live microsite demo.
     */
    public function fourth(VipPlanRepository $plans): View
    {
        $products = Product::where('status', 'active')
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->limit(8)
            ->get();

        $heroMicrosite = VipMicrosite::with(['city', 'user'])
            ->whereHas('city')
            ->whereHas('user')
            ->latest('id')
            ->first();

        return view('fourth', [
            'products' => $products,
            'plans' => $plans->activeOrdered(),
            'heroMicrosite' => $heroMicrosite,
        ]);
    }
}
