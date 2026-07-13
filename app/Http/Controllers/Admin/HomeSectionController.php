<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHomeSectionRequest;
use App\Http\Requests\Admin\UpdateHomeSectionRequest;
use App\Models\HomeSection;
use App\Repositories\HomeSectionRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class HomeSectionController extends Controller
{
    public function __construct(private readonly HomeSectionRepository $sections)
    {
    }

    public function index(): View
    {
        return view('admin.home-sections.index', ['sections' => $this->sections->allOrdered()]);
    }

    public function create(): View
    {
        return view('admin.home-sections.create');
    }

    public function store(StoreHomeSectionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('uploads', 'public');
        }

        $this->sections->create($data);

        return redirect()->route('admin.home-sections.index')->with('status', 'Section created successfully.');
    }

    public function edit(HomeSection $homeSection): View
    {
        return view('admin.home-sections.edit', ['section' => $homeSection]);
    }

    public function update(UpdateHomeSectionRequest $request, HomeSection $homeSection): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('uploads', 'public');
        }

        $this->sections->update($homeSection, $data);

        return redirect()->route('admin.home-sections.index')->with('status', 'Section updated successfully.');
    }

    public function destroy(HomeSection $homeSection): RedirectResponse
    {
        $homeSection->delete();

        return redirect()->route('admin.home-sections.index')->with('status', 'Section deleted.');
    }

    public function toggleStatus(HomeSection $homeSection): RedirectResponse
    {
        $this->sections->toggleStatus($homeSection);

        return back()->with('status', "Section status set to {$homeSection->fresh()->status}.");
    }

    public function moveUp(HomeSection $homeSection): RedirectResponse
    {
        $this->sections->moveUp($homeSection);

        return back()->with('status', 'Section moved up.');
    }

    public function moveDown(HomeSection $homeSection): RedirectResponse
    {
        $this->sections->moveDown($homeSection);

        return back()->with('status', 'Section moved down.');
    }
}
