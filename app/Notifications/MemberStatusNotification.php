<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
class MemberStatusNotification extends Notification
{
    use Queueable;
    public $status;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($status)
    {
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database', TwilioChannel::class ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toDatabase($notifiable) {

        return [
            'userData' => 'The user account is '.$this->status,
        ];
    }
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('Your Account has been '.$this->status )
                    ->action('Charge Automation', url('/'))
                    ->line('Please Contact to your Admin Thanks!');
    }

    public function toTwilio($notifiable) {
             return ; //(new TwilioSmsMessage())->content("Your Charge Automation Account has been '.$this->status);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
