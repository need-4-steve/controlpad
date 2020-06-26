<?php
namespace events;

use App\Events\PasswordNewEvent;
use App\Events\WelcomeEvent;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Order;
use \FunctionalTester;
use \Step\Api\UserAuth;

class EventCest
{

    public function seeEventWelcomeTriggered(FunctionalTester $I)
    {
        $user = User::where('role_id', 3)->first();
        event(new WelcomeEvent($user));
        $I->seeEventTriggered(new \App\Events\WelcomeEvent($user));
    }

    public function seeEventPasswordNewTriggered(FunctionalTester $I)
    {
        $user = User::where('role_id', 3)->first();
        event(new PasswordNewEvent($user));
        $I->seeEventTriggered(new \App\Events\PasswordNewEvent($user));
    }
}
