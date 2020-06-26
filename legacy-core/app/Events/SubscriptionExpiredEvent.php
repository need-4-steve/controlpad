<?php

namespace App\Events;

class SubscriptionExpiredEvent
{
    public function __construct($subscriptionUser)
    {
        $this->subscriptionUser = $subscriptionUser;
    }
}
