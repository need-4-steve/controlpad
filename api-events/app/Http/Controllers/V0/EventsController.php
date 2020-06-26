<?php

namespace App\Http\Controllers\V0;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\EventsRepositoryInterface;
use App\Event;

class EventsController extends Controller
{
    private $eventsRepo;

    private $sortableColumns = [
        'sponsor_id',
        'host_id',
        'name',
        'date',
        'sale_end',
    ];
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(EventsRepositoryInterface $eventsRepo)
    {
        $this->eventsRepo = $eventsRepo;
    }

    public function index(Request $request)
    {
        $rules = [
            'page' => 'sometimes|numeric',
            'sponsor_id' => 'sometimes|numeric',
            'per_page' => 'sometimes|numeric|min:1|max:500',
            'host_id' => 'sometimes',
            'sort_by' => 'sometimes|string|in:' . implode(',', $this->sortableColumns),
            'status' => 'sometimes|in:open,closed'
        ];
        $this->validate($request, $rules);
        $events = $this->eventsRepo->index($request->all());
        return response()->json($events);
    }

    public function create(Request $request)
    {
        $rules = Event::$rules;
        $this->validate($request, $rules);
        $params = $request->only(Event::$createFields);
        $params['sponsor_id'] = $request->user->getOwnerID();
        $event = $this->eventsRepo->create($params);
        if (!$event) {
            abort(500, 'There was an error creating the event.');
        }
        return response()->json($event);
    }

    public function show($id)
    {
        $event = $this->eventsRepo->show($id);
        if (!$event) {
            return response()->json('Event not found.', 404);
        }
        return response()->json($event);
    }

    public function edit($id, Request $request)
    {
        $rules = Event::$updateRules;
        $this->validate($request, $rules);
        $ownerId = $request->user->getOwnerID();
        $params = $request->only(Event::$updateFields);
        $event = $this->eventsRepo->edit($id, $ownerId, $params);
        if (!$event) {
            return response()->json('Event not found. Failed to update.', 404);
        }
        return response()->json($request->only(Event::$updateFields));
    }

    public function delete($id, Request $request)
    {
        $ownerId = $request->user->getOwnerID();
        $deleted = $this->eventsRepo->delete($id, $ownerId);
        if (!$deleted) {
            return response()->json('Event not found. Failed to delete.', 404);
        }
        return response()->json('Successfully deleted.');
    }
}
