<?php

namespace App\Events;

class SubscriptionRenewedEvent
{
    public function __construct($user, $subscriptionUser)
    {
        $this->user = $user;
        $this->subscriptionUser = $subscriptionUser;
    }
}
