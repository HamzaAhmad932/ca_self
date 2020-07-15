<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClientBookingNotify extends Mailable implements ShouldQueue
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
        if (isset($this->data['view'])) {

            $this->view($this->data['view']);

        } elseif (isset($this->data['markdown'])) {

            $this->markdown($this->data['markdown']);
        }
        
        if (isset($this->data['from'][0])) {

            $this->from((isset($this->data['from'][0]) ? $this->data['from'][0]: ''), (isset($this->data['from'][1]) ? $this->data['from'][1]: '').(isset($this->data['from'][0]) ? '  <'.$this->data['from'][0].'> via ' : ''));
        }
        
        if (isset($this->data['from'][0])) {

            $this->replyTo((isset($this->data['from'][0]) ? $this->data['from'][0]: ''), (isset($this->data['from'][1]) ? $this->data['from'][1]: ''));
        }

        return $this->subject($this->data['subject']);

        // Use onQueue for specific queue other than default
        // return $this->view($this->data['view'])
        // ->onQueue("email")
        // ->subject($this->data['subject']);
    }
}
