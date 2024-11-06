<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Notifications\EventNotification;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class BookingController extends Controller
{
    public function index(Request $request) {
        // Retrieve all bookings with their associated events
        $booked = Booking::with('event')->get();
        
        return response()->json($booked, 200);
    }    
    
    public function confirmBooking(Request $request)
    {
        $user = Auth::user();
    
        if (!$user->can('book events')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        $event = Event::find($request->event_id);
        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }
    
        $currentBookingsCount = $event->bookings()->count();
        $remainingCapacity = $event->capacity - $currentBookingsCount;
    
        if ($remainingCapacity <= 0) {
            return response()->json(['message' => 'No capacity available for this event'], 400);
        }
    
        $booking = Booking::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);
    
        Notification::send($user, new EventNotification($event, 'booking'));
    
        return response()->json(['message' => 'Booking successful , you will receive email confirmation', 'booking' => $booking], 200);
    }    

    public function myBooked(Request $request)
    {
        // Get the authenticated user (host)
        $host = Auth::user();
    
        // Fetch all bookings for this user, load associated events, and order by latest
        $bookings = Booking::where('user_id', $host->id)
                           ->with('event')
                           ->orderBy('created_at', 'desc')
                           ->get();
    
        return response()->json($bookings, 200);
    }
    
    public function showBooked(Request $request)
    {
        // Get the authenticated user (the host)
        $host = Auth::user();
    
        // Fetch bookings for events created by this host, ordered by latest
        $bookings = Booking::with('event')
                           ->whereHas('event', function($query) use ($host) {
                               $query->where('user_id', $host->id);
                           })
                           ->orderBy('created_at', 'desc')
                           ->get();
    
        // Map the bookings to include relevant event details
        $events = $bookings->map(function ($booking) {
            return [
                'id' => $booking->event->id,
                'image' => $booking->event->image,
                'title' => $booking->event->title,
                'capacity' => $booking->event->capacity,
                'bookings_count' => $booking->event->bookings()->count(),
                'remaining_capacity' => $booking->event->capacity - $booking->event->bookings()->count(),
            ];
        });
    
        return response()->json($events, 200);
    }    
    
}
