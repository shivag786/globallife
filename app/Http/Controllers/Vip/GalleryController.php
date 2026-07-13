<?php

namespace App\Http\Controllers\Vip;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vip\StoreGalleryItemRequest;
use App\Http\Requests\Vip\UpdateGalleryItemRequest;
use App\Models\BusinessGalleryItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class GalleryController extends Controller
{
    public function index(): View
    {
        $items = Auth::user()->vipMicrosite->galleryItems()->get();

        return view('vip.gallery.index', ['items' => $items]);
    }

    public function store(StoreGalleryItemRequest $request): RedirectResponse
    {
        BusinessGalleryItem::create([
            'vip_microsite_id' => Auth::user()->vipMicrosite->id,
            'image_path' => $request->file('image')->store('uploads', 'public'),
            'title' => $request->input('title'),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return redirect()->route('vip.gallery.index')->with('status', 'Image added.');
    }

    public function update(UpdateGalleryItemRequest $request, BusinessGalleryItem $galleryItem): RedirectResponse
    {
        $data = $request->only(['title', 'sort_order']);
        $data['is_visible'] = $request->boolean('is_visible');

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('uploads', 'public');
        }

        $galleryItem->update($data);

        return redirect()->route('vip.gallery.index')->with('status', 'Image updated.');
    }

    public function destroy(BusinessGalleryItem $galleryItem): RedirectResponse
    {
        abort_unless($galleryItem->vip_microsite_id === Auth::user()->vipMicrosite->id, 403);

        $galleryItem->delete();

        return redirect()->route('vip.gallery.index')->with('status', 'Image deleted.');
    }
}
