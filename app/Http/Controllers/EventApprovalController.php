<?php
namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventApproval; 
use App\Notifications\EventRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventApprovalController extends Controller
{
    /**
     * Approve an event
     */
    public function approve(Event $event)
    {
        // Check if the user has permission to approve events
        if (!Auth::user()->can('approve events')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Check if the user has already approved or rejected this event
        $existingApproval = EventApproval::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingApproval) {
            return response()->json(['message' => 'You have already submitted an action for this event.'], 400);
        }

        // Record the approval action
        EventApproval::create([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'action' => 'approved',
        ]);

        return response()->json(['message' => 'Event approved successfully', 'event' => $event], 200);
    }

    /**
     * Reject an event
     */
    public function reject(Event $event)
    {
        // Check if the user has permission to approve/reject events
        if (!Auth::user()->can('approve events')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if the user has already approved or rejected this event
        $existingApproval = EventApproval::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingApproval) {
            return response()->json(['message' => 'You have already submitted an action for this event.'], 400);
        }

        // // Create the notification instance
        // $notification = new EventRejectedNotification($event);

        // // Send rejection notification to the event creator
        // $event->user->notify($notification);

        // Record the rejection action
        EventApproval::create([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'action' => 'rejected',
        ]);

        return response()->json(['message' => 'Event rejected successfully'], 200);
    }
}
