<?php

use App\Models\SubscriptionLine;
use App\Models\Subscription;

class SubscriptionLineTest extends TestCase
{
    public function testCreate()
    {
        $request = factory(SubscriptionLine::class)->make()->toArray();
        $subscription = Subscription::orderBy('created_at', 'desc')->first();
        $request['subscription_pid'] = $subscription->pid;
        $response = $this->basicRequest('POST', '/api/v0/subscription-lines', $request);
        $response->assertResponseStatus(200);
        unset($request['subscription_pid']);
        unset($request['items']);
        $this->seeInDatabase('autoship_subscription_lines', $request);
        $response->seeJson($request);
    }

    public function testUpdate()
    {
        $subscriptionLine = factory(SubscriptionLine::class)->create()->toArray();
        $request = factory(SubscriptionLine::class)->make()->toArray();
        $response = $this->basicRequest('PATCH', '/api/v0/subscription-lines/'.$subscriptionLine['pid'], $request);
        $response->assertResponseStatus(200);
        unset($request['items']);
        unset($subscriptionLine['items']);
        $this->seeInDatabase('autoship_subscription_lines', $request);
        $this->notSeeInDatabase('autoship_subscription_lines', $subscriptionLine);
        $response->seeJson($request);
    }

    public function testFind()
    {
        $subscriptionLine = factory(SubscriptionLine::class)->create()->toArray();
        $response = $this->basicRequest('GET', '/api/v0/subscription-lines/'.$subscriptionLine['pid']);
        unset($subscriptionLine['items']);
        $response->assertResponseStatus(200);
        $response->seeJson($subscriptionLine);
    }

    public function testDelete()
    {
        $subscriptionLine = factory(SubscriptionLine::class)->create()->toArray();
        $response = $this->basicRequest('DELETE', '/api/v0/subscription-lines/'.$subscriptionLine['pid']);
        $response->assertResponseStatus(200);
        unset($subscriptionLine['items']);
        $this->notSeeInDatabase('autoship_subscription_lines', array_merge($subscriptionLine, ['deleted_at' => null]));
    }
}
