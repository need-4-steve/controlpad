<?php

namespace App\Listeners;

use App\Services\WebhookServiceInterface;
use App\Services\NotificationServiceInterface;
use CPCommon\Events\GenericEvent;

class GenericEventListener
{

    private $webhookService;
    private $notificationService;

    public function __construct(
        WebhookServiceInterface $webhookService,
        NotificationServiceInterface $notificationService
    ) {
        $this->webhookService = $webhookService;
        $this->notificationService = $notificationService;
    }

    /**
    * @param String $type
    * @param GenericEvent $event
    */
    public function onEvent($type, array $events)
    {
        $this->webhookService->sendHooks($events[0]);
        $this->notificationService->sendNotifications($events[0]);
    }

    public function subscribe($events)
    {
        $events->listen(
            'generic-event.*',
            'App\Listeners\GenericEventListener@onEvent'
        );
    }
}
