<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->can('view events')) {
            if ($user->hasRole('admin')) {
                $events = Event::with('category')->get();
            } else if ($user->hasRole('host')) {
                $events = Event::with('category')->where('user_id', $user->id)->get();
            } else if ($user->hasRole('attendee')) {
                $events = Event::with('category')
                    ->join('event_approvals', 'event_id', '=', 'event_approvals.event_id') // Join with event_approval table
                    ->where('event_approvals.action', 'approved') // Filter for approved actions
                    ->select('events.*') // Select the event columns
                    ->get();
            }
            

            return response()->json([
                'message' => 'Events retrieved successfully',
                'events' => $events,
            ], 200);
        }

        return response()->json([
            'message' => 'Unauthorized access to events',
        ], 403);
    }
    public function events()
    {
        $events = Event::with('category')
            ->whereHas('approvals', function ($query) {
                $query->where('action', 'approved');
            })
            ->get();

        return response()->json([
            'message' => 'Events retrieved successfully',
            'events' => $events,
        ], 200);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            "title" => "required|max:255",
            "description" => "required",
            "category_id" => "required|exists:categories,id",
            "location" => "required|max:255",
            "start_date" => "required|date",
            "end_date" => "required|date|after_or_equal:start_date",
            "ticket_price" => "required|numeric",
            "capacity" => "required|integer",
        ]);

        $data['start_date'] = \Carbon\Carbon::parse($data['start_date'])->format('Y-m-d H:i:s');
        $data['end_date'] = \Carbon\Carbon::parse($data['end_date'])->format('Y-m-d H:i:s');

        if (!Auth::user()->can('create events')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data['user_id'] = Auth::id();
        $data['category_id'];

        $event = Event::create($data);

        return response()->json([
            'message' => 'Event created successfully',
            'event' => $event,
        ], 201);
    }

    public function show($id)
    {
        $event = Event::with(['user', 'category'])->findOrFail($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Count the number of tickets booked for this event
        $bookedTickets = Booking::where('event_id', $event->id)->count();

        // Add remaining capacity to the event data
        $remainingCapacity = $event->capacity - $bookedTickets;

        // Include booked tickets and remaining capacity in the response
        $event->booked_tickets = $bookedTickets;
        $event->remaining_capacity = $remainingCapacity;

        return response()->json($event, 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $event = Event::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        if (!Auth::user()->can('manage events')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            "title" => "required|max:255",
            "description" => "required",
            "category_id" => "required|exists:categories,id",
            "location" => "required|max:255",
            "start_date" => "required|date",
            "end_date" => "required|date|after_or_equal:start_date",
            "ticket_price" => "required|numeric",
            "capacity" => "required|integer",
        ]);

        $data['start_date'] = \Carbon\Carbon::parse($data['start_date'])->format('Y-m-d H:i:s');
        $data['end_date'] = \Carbon\Carbon::parse($data['end_date'])->format('Y-m-d H:i:s');

        $event->update($data);

        return response()->json([
            'message' => 'Event updated successfully',
            'event' => $event,
        ]);
    }

    public function destroy(Request $request, Event $event)
    {
        if (!Auth::user()->can('manage events')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event->delete();
        return ["message" => "Event was deleted"];
    }
}
