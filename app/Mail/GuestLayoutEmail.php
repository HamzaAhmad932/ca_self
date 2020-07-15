<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class GuestLayoutEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        /*
        |--------------------------------------------------------------------------
        | Send Tags to MailGun -- For Analytics Opened/Clicked per email type
        |--------------------------------------------------------------------------
        */
        if(isset($this->data['email_tags_for_tracking']) && count($this->data['email_tags_for_tracking'])>0) {
            $all_tags = $this->data['email_tags_for_tracking'];
            $this->withSwiftMessage(function ($message) use($all_tags) {
                $headers = $message->getHeaders();
                foreach ($all_tags as $tag)
                {
                    $headers->addTextHeader("X-Mailgun-Tag", $tag);
                }
            });
        }

        $this->view('emails.guest_email_theme');
        $this->subject($this->data['subject']);
        $this->from((isset($this->data['from'][0]) ? $this->data['from'][0]: ''), (isset($this->data['from'][1]) ? $this->data['from'][1]: ''));
        $this->replyTo((isset($this->data['reply_to'][0]) ? $this->data['reply_to'][0]: ''), (isset($this->data['reply_to'][1]) ? $this->data['reply_to'][1]: ''));
    }

}