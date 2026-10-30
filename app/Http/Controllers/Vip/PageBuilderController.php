<?php

namespace App\Http\Controllers\Vip;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vip\UpdatePageBuilderRequest;
use App\Services\PageBuilderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PageBuilderController extends Controller
{
    public function __construct(private readonly PageBuilderService $builder)
    {
    }

    public function edit(): View
    {
        $microsite = Auth::user()->vipMicrosite;

        return view('vip.page-builder.edit', [
            'microsite' => $microsite,
            'state' => $this->builder->state($microsite),
        ]);
    }

    public function update(UpdatePageBuilderRequest $request): JsonResponse
    {
        $microsite = $request->user()->vipMicrosite;

        $this->builder->save($microsite, $request->validated('sections'));

        // Hand the saved page back so new cards pick up their real ids and a
        // second save updates them instead of creating them again.
        return response()->json([
            'message' => 'Your page has been saved.',
            'state' => $this->builder->state($microsite->fresh()),
        ]);
    }

    /**
     * A cropped image from the builder. Stored straight away so the editor can
     * show it; it only becomes part of the page when the owner saves.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $path = $this->builder->storeUpload($request->user()->vipMicrosite, $request->file('image'));

        return response()->json([
            'path' => $path,
            'url' => asset('storage/'.$path),
        ]);
    }
}
