<?php

namespace App\Http\Controllers\Vip;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vip\StoreServiceRequest;
use App\Http\Requests\Vip\UpdateServiceRequest;
use App\Models\BusinessService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Auth::user()->vipMicrosite->services()->get();

        return view('vip.services.index', ['services' => $services]);
    }

    public function create(): View
    {
        return view('vip.services.create');
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $data = $this->prepareData($request);
        $data['vip_microsite_id'] = Auth::user()->vipMicrosite->id;

        BusinessService::create($data);

        return redirect()->route('vip.services.index')->with('status', 'Service created successfully.');
    }

    public function edit(BusinessService $service): View
    {
        abort_unless($service->vip_microsite_id === Auth::user()->vipMicrosite->id, 403);

        return view('vip.services.edit', ['service' => $service]);
    }

    public function update(UpdateServiceRequest $request, BusinessService $service): RedirectResponse
    {
        $service->update($this->prepareData($request));

        return redirect()->route('vip.services.index')->with('status', 'Service updated successfully.');
    }

    public function destroy(BusinessService $service): RedirectResponse
    {
        abort_unless($service->vip_microsite_id === Auth::user()->vipMicrosite->id, 403);

        $service->delete();

        return redirect()->route('vip.services.index')->with('status', 'Service deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareData(StoreServiceRequest|UpdateServiceRequest $request): array
    {
        $data = $request->validated();
        $data['slug'] = $request->input('slug');
        $data['tags'] = $data['tags'] ?? null ? array_map('trim', explode(',', $data['tags'])) : null;
        $data['show_pricing'] = $request->boolean('show_pricing');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['show_book_now'] = $request->boolean('show_book_now');
        $data['meta_title'] = $data['name'];
        $data['meta_description'] = $data['short_description'] ?? Str::limit(strip_tags($data['long_description'] ?? ''), 160);
        $data['meta_keywords'] = $data['category'] ?? null;

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('uploads', 'public');
        }

        return $data;
    }
}
