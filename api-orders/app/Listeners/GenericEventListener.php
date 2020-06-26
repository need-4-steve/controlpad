<?php

namespace App\Listeners;

use CPCommon\Events\GenericEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenericEventListener implements ShouldQueue
{

    public $queue = 'events';

    public function __construct()
    {
        // Do nothing
    }


    public function handle(GenericEvent $event)
    {
        // We don't handle these events inside this service
    }
}
