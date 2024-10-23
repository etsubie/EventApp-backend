<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventRejectedNotification extends Notification
{
    use Queueable;

    protected $event;

    /**
     * Create a new notification instance.
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];  // Store in database and send email
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Event Has Been Rejected')
            ->greeting('Hello ' . $this->event->user->name . ',')
            ->line('We regret to inform you that your event "' . $this->event->title . '" has been rejected.')
            ->action('View Event Details', url('/events/' . $this->event->id));
    }

    public function toArray($notifiable)
    {
        return [
            'event_id' => $this->event->id,
            'message' => 'Your event "' . $this->event->title . '" has been rejected.',
        ];
    }

}
