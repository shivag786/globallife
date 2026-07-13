<?php

namespace App\Http\Controllers\Vip;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vip\StoreVideoRequest;
use App\Models\BusinessVideo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller
{
    public function index(): View
    {
        $videos = Auth::user()->vipMicrosite->videos()->get();

        return view('vip.videos.index', ['videos' => $videos]);
    }

    public function store(StoreVideoRequest $request): RedirectResponse
    {
        $youtubeId = BusinessVideo::extractYoutubeId($request->input('youtube_url'));

        BusinessVideo::create([
            'vip_microsite_id' => Auth::user()->vipMicrosite->id,
            'youtube_url' => $request->input('youtube_url'),
            'thumbnail_url' => "https://img.youtube.com/vi/{$youtubeId}/hqdefault.jpg",
            'title' => $request->input('title'),
            'sort_order' => Auth::user()->vipMicrosite->videos()->count(),
        ]);

        return redirect()->route('vip.videos.index')->with('status', 'Video added.');
    }

    public function destroy(BusinessVideo $video): RedirectResponse
    {
        abort_unless($video->vip_microsite_id === Auth::user()->vipMicrosite->id, 403);

        $video->delete();

        return redirect()->route('vip.videos.index')->with('status', 'Video removed.');
    }
}
