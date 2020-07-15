<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AutoChargeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (isset($this->data['from'][0])) {
           
            $this->from((isset($this->data['from'][0]) ? $this->data['from'][0]: ''), (isset($this->data['from'][1]) ? $this->data['from'][1]: '').(isset($this->data['from'][0]) ? '  <'.$this->data['from'][0].'> via ' : ''));
        }
       
        if (isset($this->data['from'][0])) {
           
            $this->replyTo((isset($this->data['from'][0]) ? $this->data['from'][0]: ''), (isset($this->data['from'][1]) ? $this->data['from'][1]: ''));
        }

        return $this->markdown('emails.ChargeMessage');
    }
}
