<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Repositories\EventRepository;
use Illuminate\Contracts\View\View;

class EventController extends Controller
{
    public function index(EventRepository $events): View
    {
        return view('events.index', ['events' => $events->publishedPaginated()]);
    }

    public function show(Event $event): View
    {
        abort_unless($event->status === 'active', 404);

        return view('events.show', ['event' => $event]);
    }
}
