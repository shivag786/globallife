<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMediaItemRequest;
use App\Models\MediaItem;
use App\Repositories\MediaRepository;
use App\Services\PermissionMatrixService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class MediaController extends Controller
{
    public function __construct(private readonly MediaRepository $media)
    {
    }

    public function index(): View
    {
        if (! PermissionMatrixService::userCanAccessModule(auth()->user(), 'media')) {
            throw new AccessDeniedHttpException;
        }

        return view('admin.media.index', ['items' => $this->media->allOrdered()]);
    }

    public function store(StoreMediaItemRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['file_path'] = $request->file('file')->store('uploads', 'public');
        unset($data['file']);

        $this->media->create($data);

        return redirect()->route('admin.media.index')->with('status', 'Media uploaded successfully.');
    }

    public function destroy(MediaItem $mediaItem): RedirectResponse
    {
        abort_unless(auth()->user()->can('media.delete'), 403);

        $mediaItem->delete();

        return redirect()->route('admin.media.index')->with('status', 'Media deleted.');
    }

    public function toggleStatus(MediaItem $mediaItem): RedirectResponse
    {
        abort_unless(auth()->user()->can('media.edit'), 403);

        $this->media->toggleStatus($mediaItem);

        return back()->with('status', "Media status set to {$mediaItem->fresh()->status}.");
    }
}
