<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ClientLayoutEmail extends Mailable implements ShouldQueue
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
//        if(isset($this->data['companyImage']) && strpos($this->data['companyImage'], 'no_image.png') !== false) {
//
//            $this->data['companyImage'] = '';
//        }
//
//        if (isset($this->data['view'])) {
//
//            $this->view($this->data['view']);
//
//        } elseif (isset($this->data['markdown'])) {
//
//            $this->markdown($this->data['markdown']);
//        }
//
//        if (isset($this->data['noReply']) && ($this->data['noReply'] === true)) {
//
//            $this->from(config('mail.noReply.address'), config('mail.noReply.name'));
//
//        } else if (isset($this->data['from'][0]) && ( !isset($this->data['noReply']) || ($this->data['noReply'] !== true) )) {
//
//            $this->from((isset($this->data['from'][0]) ? $this->data['from'][0]: ''), (isset($this->data['from'][1]) ? $this->data['from'][1]: '').(isset($this->data['from'][0]) ? ' <'.$this->data['from'][0].'> via ' : ''));
//            $this->replyTo((isset($this->data['from'][0]) ? $this->data['from'][0]: ''), (isset($this->data['from'][1]) ? $this->data['from'][1]: ''));
//        }
//
//        return $this->subject($this->data['subject']);

        $this->view('emails.client_email_theme');
        $this->subject($this->data['subject']);
        $this->from(config('mail.noReply.address'), config('mail.noReply.name'));
    }

}