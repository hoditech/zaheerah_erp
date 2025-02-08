<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    // Get all events
    public function index()
    {
        $events = Event::all();
        return response()->json($events);
    }

    // Store a new event
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
        ]);

        $event = Event::create($request->all());

        // Log the activity
        log_activity('Create Event', 'Created event: ' . $event->title);

        return response()->json($event, 201);
    }

    // Update an event
    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'date' => 'sometimes|date',
        ]);

        $event->update($request->all());

        // Log the activity
        log_activity('Update Event', 'Updated event: ' . $event->title);

        return response()->json($event);
    }

    // Delete an event
    public function destroy(Event $event)
    {
        // Log the activity before deleting
        log_activity('Delete Event', 'Deleted event: ' . $event->title);

        $event->delete();
        return response()->json(null, 204);
    }
}