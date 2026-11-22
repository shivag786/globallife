<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Announcement;
use App\Support\HrAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** The employee notice board. */
class AnnouncementController extends Controller
{
    public function index(): View
    {
        HrAccess::ability('employees.edit');

        return view('admin.hr.announcements.index', [
            'announcements' => Announcement::with('publisher')->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $actor = HrAccess::ability('employees.edit');

        Announcement::create([...$this->validated($request), 'is_active' => true, 'published_by' => $actor->id]);

        return back()->with('status', 'Announcement published to all employees.');
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        $announcement->update([...$this->validated($request), 'is_active' => $request->boolean('is_active')]);

        return back()->with('status', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        $announcement->delete();

        return back()->with('status', 'Announcement deleted.');
    }

    /** @return array{title: string, body: string} */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ]);
    }
}
