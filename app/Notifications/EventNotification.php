<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventNotification extends Notification
{
    use Queueable;

    protected $event;
    protected $type;  

    /**
     * Create a new notification instance.
     */
    public function __construct($event, $type)
    {
        $this->event = $event;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail'];  
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        if ($this->type === 'booking') {
            return $this->bookingConfirmationMail($notifiable);
        } elseif ($this->type === 'approval') {
            return $this->eventApprovalMail($notifiable);
        }

        return (new MailMessage)->line('Unknown notification type.');
    }

    /**
     * Booking confirmation email
     */
    protected function bookingConfirmationMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Booking Confirmation for ' . $this->event->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Thank you for booking the event "' . $this->event->title . '".')
            ->line('You can view the event details below:')
            ->action('View Event', env('VITE_URL') . '/events/' . $this->event->id)
            ->line('We look forward to seeing you there!');
    }

    /**
     * Event approval email
     */
    protected function eventApprovalMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Event Has Been Rejected')
            ->greeting('Hello ' . $this->event->user->name . ',')
            ->line('Unfortunately, your event "' . $this->event->title . '" has been rejected.')
            ->action('View Event Details', env('VITE_URL') . '/events/' . $this->event->id);
        }
}
