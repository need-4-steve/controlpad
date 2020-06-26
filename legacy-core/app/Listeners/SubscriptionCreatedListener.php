<?php

namespace App\Listeners;

use App\Events\SubscriptionCreatedEvent;

/**
 * This listener is for actionable change in subscription status
 * Other listeners are for emails or should have used a service instead
*/
class SubscriptionCreatedListener
{

    public function handle(SubscriptionCreatedEvent $event)
    {
        // Check client to see if myzoomlive, then post user to zoom
        if (!empty(env('ZOOM_API_KEY'))) {
            // This is currently a dirty one off for a client
            // For now we aren't going to worry about operating out of a queue to prevent blank jobs from posting for all other clients
            // In the future this may be used for webhooks, will require some structure change in that case
            // Putting MyZoomLive code in here to make sure it's obvious what is going on, and what will need migrated if this is changed
            (new \App\Services\Zoom\ZoomService)->createZoomUser($event->user);
        }
    }
}
