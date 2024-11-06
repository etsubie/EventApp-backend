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
                // Fetch all events, ordered by latest
                $events = Event::with('category')->orderBy('created_at', 'desc')->get();
            } else if ($user->hasRole('host')) {
                // Fetch host-specific events, ordered by latest
                $events = Event::with('category')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
            }
            //  else if ($user->hasRole('attendee')) {
            //     $events = Event::with('category')
            //     ->whereHas('approvals', function ($query) {
            //         $query->where('action', 'approved');
            //     })->orderBy('created_at', 'desc')
            //     ->get();
            // }
    
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
        try {
            $events = Event::with('category')
                ->whereHas('approvals', function ($query) {
                    $query->where('action', 'approved');
                })
                ->orderBy('created_at', 'desc')
                ->get();
                
            return response()->json([
                'message' => 'Events retrieved successfully',
                'events' => $events,
            ], 200);
        } catch (e) {
            return response()->json(['error' => 'Failed to retrieve events'], 500);
        }
    }
        public function store(Request $request)
    {
        // Validate the incoming request data
        $data = $request->validate([
            "title" => "required|max:255",
            "description" => "required",
            "category_id" => "required|exists:categories,id",
            "location" => "required|max:255",
            "start_date" => "required|date",
            "end_date" => "required|date|after_or_equal:start_date",
            "ticket_price" => "required|numeric",
            "capacity" => "required|integer",
            "image" => "nullable|string" // Expect Base64 string for the image
        ]);
    
        // Format the start and end dates
        $data['start_date'] = \Carbon\Carbon::parse($data['start_date'])->format('Y-m-d H:i:s');
        $data['end_date'] = \Carbon\Carbon::parse($data['end_date'])->format('Y-m-d H:i:s');
    
        // Handle Base64 image decoding
        if ($request->has('image') && $request->image) {
            $image = $request->image; // Base64 encoded image
        
            // Extract MIME type and Base64 data
            preg_match('/^data:image\/(\w+);base64,/', $image, $type);
            if (empty($type[1])) {
                throw new \Exception('Invalid image type');
            }
        
            // Create a dynamic file extension based on the MIME type
            $extension = $type[1]; // jpg ,png....
            $imageName = 'event_image_' . time() . '.' . $extension; // Dynamically set the extension
        
            // Remove the Base64 header part
            $image = str_replace('data:image/' . $extension . ';base64,', '', $image);
            $image = str_replace(' ', '+', $image);
        
            // Decode and store the image
            \File::put(public_path('images/') . $imageName, base64_decode($image));
        
            // Add the image path to the $data array
            $data['image'] = 'images/' . $imageName;
        }
        
        // Assign the authenticated user's ID to the user_id field
        $data['user_id'] = Auth::id();
    
        $event = Event::create($data);
    
        // Return a success response with the created event
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
    
        // Check for event management permission
        if (!Auth::user()->can('manage events')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        // Validate the incoming request data
        $data = $request->validate([
            "title" => "required|max:255",
            "description" => "required",
            "category_id" => "required|exists:categories,id",
            "location" => "required|max:255",
            "start_date" => "required|date",
            "end_date" => "required|date|after_or_equal:start_date",
            "ticket_price" => "required|numeric",
            "capacity" => "required|integer",
            "image" => "nullable|string",  // Allow Base64 image strings
        ]);
    
        // Format the start and end dates
        $data['start_date'] = \Carbon\Carbon::parse($data['start_date'])->format('Y-m-d H:i:s');
        $data['end_date'] = \Carbon\Carbon::parse($data['end_date'])->format('Y-m-d H:i:s');
    
      // Handle Base64 image decoding
      if ($request->has('image') && $request->image) {
        $image = $request->image; // Base64 encoded image
    
        // Extract MIME type and Base64 data
        preg_match('/^data:image\/(\w+);base64,/', $image, $type);
        if (empty($type[1])) {
            throw new \Exception('Invalid image type');
        }
    
        // Create a dynamic file extension based on the MIME type
        $extension = $type[1]; // This will be 'png', 'jpg', etc.
        $imageName = 'event_image_' . time() . '.' . $extension; // Dynamically set the extension
    
        // Remove the Base64 header part
        $image = str_replace('data:image/' . $extension . ';base64,', '', $image);
        $image = str_replace(' ', '+', $image);
    
        // Decode and store the image
        \File::put(public_path('images/') . $imageName, base64_decode($image));
    
        // Add the image path to the $data array
        $data['image'] = 'images/' . $imageName;
    }
    
        // Update the event with validated data
        $event->update($data);
    
        // Return a success response with the updated event
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
