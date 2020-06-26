<?php

use App\Subscription;
use App\Address;
use Carbon\Carbon;

class SubscriptionTest extends TestCase
{
    protected static $subscriptionStructure = [
        'pid',
        'user_pid',
        'ends_at',
        'auto_renew',
        'fail_description',
        'created_at',
        'updated_at',
        'first_name',
        'last_name',
    ];

    public function testSubscriptionIndex()
    {
        $user = $this->createUser('Rep');
        $subscription = factory(Subscription::class)->create([
            'user_id' => $user->id,
            'user_pid' => $user->pid,
        ]);
        $response = $this->basicRequest('GET', '/api/v0/subscriptions/', ['order_by' => '-created_at']);
        $response->assertResponseStatus(200);
        $response->seeJsonStructure(['data' =>
            [
                '*' => $this::$subscriptionStructure
            ]
        ]);
        unset($subscription->subscription_id);
        $response->seeJson($subscription->toArray());
    }

    public function testSubscriptionUpdate()
    {
        $user = $this->createUser('Rep');
        $subscription = factory(Subscription::class)->create([
            'user_id' => $user->id,
            'user_pid' => $user->pid,
        ]);
        $subscriptionUpdate = factory(Subscription::class)->make([
            'user_id' => $user->id,
            'user_pid' => $user->pid,
        ]);
        $response = $this->basicRequest('PATCH', '/api/v0/subscriptions/'.$subscription->pid, $subscriptionUpdate->toArray());
        $response->assertResponseStatus(200);
        $response->seeJson($subscriptionUpdate->toArray());
    }
}
