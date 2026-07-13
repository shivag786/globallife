<?php

namespace App\Http\Controllers\Vip;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vip\StoreBannerRequest;
use App\Models\BusinessBanner;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BannerController extends Controller
{
    public function index(): View
    {
        $banners = Auth::user()->vipMicrosite->banners()->get();

        return view('vip.banners.index', [
            'desktopBanners' => $banners->where('device', 'desktop'),
            'mobileBanners' => $banners->where('device', 'mobile'),
        ]);
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        BusinessBanner::create([
            'vip_microsite_id' => Auth::user()->vipMicrosite->id,
            'device' => $request->input('device'),
            'image_path' => $request->file('image')->store('uploads', 'public'),
            'heading' => $request->input('heading'),
            'subheading' => $request->input('subheading'),
            'button_text' => $request->input('button_text'),
            'button_link' => $request->input('button_link'),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return redirect()->route('vip.banners.index')->with('status', 'Banner added.');
    }

    public function update(Request $request, BusinessBanner $banner): RedirectResponse
    {
        abort_unless($banner->vip_microsite_id === Auth::user()->vipMicrosite->id, 403);

        $banner->update(['is_visible' => $request->boolean('is_visible')]);

        return redirect()->route('vip.banners.index')->with('status', 'Banner updated.');
    }

    public function destroy(BusinessBanner $banner): RedirectResponse
    {
        abort_unless($banner->vip_microsite_id === Auth::user()->vipMicrosite->id, 403);

        $banner->delete();

        return redirect()->route('vip.banners.index')->with('status', 'Banner removed.');
    }
}
