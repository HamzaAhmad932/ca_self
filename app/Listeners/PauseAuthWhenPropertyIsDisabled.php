<?php

namespace App\Listeners;

use App\Events\PropertyDisabledEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PauseAuthWhenPropertyIsDisabled {

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {
    }

    /**
     * Handle the event.
     *
     * @param  PropertyDisabledEvent  $event
     * @return void
     */
    public function handle(PropertyDisabledEvent $event) {

    }
}
