<?php

namespace App\Events\Emails;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * Class SendEmailNewEvent
 * @package App\Events\Emails
 */
class EmailEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $email_type;
    public $model_id;
    public $extras;

    /**
     * EmailEvent constructor.
     * @param $email_type
     * @param $model_id
     * @param array $extras
     */
    public function __construct($email_type,  $model_id, $extras=[])
    {
        $this->email_type = $email_type;
        $this->model_id = $model_id;
        //some emails need exception message, code etc extra information so we will pass it as array
        $this->extras = $extras;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
