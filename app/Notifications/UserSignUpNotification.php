<?php

namespace App\Notifications;

use App\User;
use App\Mail\GenericEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Class UserSignUpJob
 * @package App\Notifications
 *
 * <b>This class will be used when first user sign up.</b>
 *
 */
class UserSignUpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $user;
     

    /**
     * Create a new notification instance.
     *
     * @param User $user
     */
    public function __construct(User $user) {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable) {
        return ['database', 'mail'];
    } 

    /**
     * @param $notifiable
     * Should return a plain PHP array.
     * The returned array will be encoded as JSON and stored in the data column of your notifications table
     * @return array
     */
    public function toDatabase($notifiable) {

        return [
            'userData' => $this->user->toArray(),
        ];
    }

    public function toMail($notifiable)
    {

        return (new MailMessage)
            ->markdown('emails.admin-mail', ['data'=> $this->user])
            ->subject('CA: new user SignUp - ' . $this->user->name);
    }


    /**
     * Get the array representation of the notification.
     * Also used by the broadcast channel to determine which data to broadcast to your JavaScript client
     *
     * If you would like to have two different array representations for the database and broadcast channels,
     * you should define a toDatabase method instead of a toArray method.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    // public function toArray($notifiable) {
    //     return ['user'=>'dummy user 2'];
    // }
}
