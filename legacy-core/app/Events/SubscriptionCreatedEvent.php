<?php

namespace App\Events;

class SubscriptionCreatedEvent
{
    public function __construct($user, $subscriptionUser)
    {
        $this->user = $user;
        $this->subscriptionUser = $subscriptionUser;
    }
}
