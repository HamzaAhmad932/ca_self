<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;


class DatabaseNotification extends Notification
{
    use Queueable;
    private $subscription;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($letter)
    {
        //
        $this->subscription = $letter;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        //return ['mail'];
        return ['database','broadcast'];

    }

   

    public function toDatabase($notifiable)
    {
        return [
            'letter' => $this->subscription
        ];
    }
    
    public function toBroadcast($notifiable)
    {

    
        return new BroadcastMessage([

            'letter' => $this->subscription, 
            'count' => $notifiable->unreadNotifications->count(),
            'name' => 'GM',

        ]);


    }
}
