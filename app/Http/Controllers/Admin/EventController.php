<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Http\Requests\Admin\UpdateEventRequest;
use App\Models\Event;
use App\Repositories\EventRepository;
use App\Services\PermissionMatrixService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EventController extends Controller
{
    public function __construct(private readonly EventRepository $events)
    {
    }

    public function index(): View
    {
        $this->ensureModuleAccess();

        return view('admin.events.index', ['events' => $this->events->allOrdered()]);
    }

    public function create(): View
    {
        $this->ensureModuleAccess();

        return view('admin.events.create');
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('uploads', 'public');
        }

        $this->events->create($data);

        return redirect()->route('admin.events.index')->with('status', 'Event created successfully.');
    }

    public function edit(Event $event): View
    {
        $this->ensureModuleAccess();

        return view('admin.events.edit', ['event' => $event]);
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('uploads', 'public');
        }

        $this->events->update($event, $data);

        return redirect()->route('admin.events.index')->with('status', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        abort_unless(auth()->user()->can('events.delete'), 403);

        $event->delete();

        return redirect()->route('admin.events.index')->with('status', 'Event deleted.');
    }

    private function ensureModuleAccess(): void
    {
        if (! PermissionMatrixService::userCanAccessModule(auth()->user(), 'events')) {
            throw new AccessDeniedHttpException;
        }
    }
}
