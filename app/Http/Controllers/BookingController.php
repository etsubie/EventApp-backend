<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function confirmBooking(Request $request)
    {
        // Check if the user has permission to book events
        if (!Auth::user()->can('book events')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Find the event by ID
        $event = Event::find($request->event_id);
        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Calculate the current number of bookings
        $currentBookingsCount = $event->bookings()->count();
        $remainingCapacity = $event->capacity - $currentBookingsCount;

        // Check if there is capacity available
        if ($remainingCapacity <= 0) {
            return response()->json(['message' => 'No capacity available for this event'], 400);
        }

        // Store the booking after successful payment
        $booking = Booking::create([
            'user_id' => Auth::id(),
            'event_id' => $event->id,
        ]);

        return response()->json(['message' => 'Booking successful', 'booking' => $booking]);
    }

    public function myBooked(Request $request)
    {
        // Get the authenticated user (host)
        $host = Auth::user();
    
        // Fetch all bookings for this user and load the associated events
        $bookings = Booking::where('user_id', $host->id)
                           ->with('event')
                           ->get();
        return response()->json($bookings, 200);
    }
    
    public function showBooked(Request $request)
    {
        // Get the authenticated user (the host)
        $host = Auth::user();
    
        // Fetch bookings for events created by this host
        $bookings = Booking::with('event') 
                           ->whereHas('event', function($query) use ($host) {
                               $query->where('user_id', $host->id); 
                           })
                           ->get();
    
        // Map the bookings to include relevant event details
        $events = $bookings->map(function ($booking) {
            return [
                'id' => $booking->event->id,
                'title' => $booking->event->title,
                'capacity' => $booking->event->capacity,
                'bookings_count' => $booking->event->bookings()->count(),
                'remaining_capacity' => $booking->event->capacity - $booking->event->bookings()->count(),
            ];
        });
    
        return response()->json($events, 200);
    }
    
}
