<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class test extends Mailable
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
        //------_For guest---------
//        $this->view('emails.new_theme');
//        $this->from($address = config('mail.from.address'), $name = '300 Front Street'); // Property name and support@chargeautomation.com
//        $this->replyTo('support@thelifesuites.com', '300 Front Street'); // user account email and Property Name
//        $this->subject('New Booking | 06 Feb | Sam Kozey | 16411189');

        $this->view('emails.new_theme');
        $this->from(config('mail.noReply.address'), config('mail.noReply.name'));
        $this->subject('🔺Booking Cancelled | 08 Jan | Rylan Murazik | 16411432');
    }
}
