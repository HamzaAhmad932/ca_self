<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Booking_info;

class BookingPaymentAttempt extends Mailable implements ShouldQueue
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

        if (isset($this->data['from'][0])) {

            $this->from((isset($this->data['from'][0]) ? $this->data['from'][0]: ''), (isset($this->data['from'][1]) ? $this->data['from'][1]: '').(isset($this->data['from'][0]) ? '  <'.$this->data['from'][0].'> via ' : ''));
        }
        
        if (isset($this->data['from'][0])) {
            
            $this->replyTo((isset($this->data['from'][0]) ? $this->data['from'][0]: ''), (isset($this->data['from'][1]) ? $this->data['from'][1]: ''));
        }

        return $this->view('emails.payment_attempt_email')->with([
        'name' => $this->data['name'],
        'status' => $this->data['status'],
        'amount' => $this->data['amount'],
        'url' => $this->data['url'], ]);



    }
}
