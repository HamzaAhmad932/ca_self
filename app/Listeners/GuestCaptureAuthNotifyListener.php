<?php

namespace App\Listeners;

use App\Events\GuestCaptureAuthNotifyEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class GuestCaptureAuthNotifyListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  GuestCaptureAuthNotifyEvent  $event
     * @return void
     */
    public function handle(GuestCaptureAuthNotifyEvent $event)
    {
        //
    }
}
