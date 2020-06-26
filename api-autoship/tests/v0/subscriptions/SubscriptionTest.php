<?php

use App\Models\Subscription;
use Carbon\Carbon;

class SubscriptionTest extends TestCase
{
    public function testCreate()
    {
        $request = factory(Subscription::class)->make()->toArray();
        $request['cart_pid'] = 'test';
        $response = $this->basicRequest('POST', '/api/v0/subscriptions', $request);
        $response->assertResponseStatus(200);
        $object = json_decode($response->response->getContent());
        $subscription = Subscription::where('pid', $object->pid)->first();
        $response->assertTrue(isset($subscription));
        // need to compare discounts then unset it because of how laravel encodes json is slightly different then how phpunit encodes json
        $response->assertTrue($subscription->discounts == $request['discounts']);
        unset($request['cart_pid']);
        $response->seeJson($request);
        unset($request['discounts']);
        $this->seeInDatabase('autoship_subscriptions', $request);
    }

    public function testUpdate()
    {
        $subscription = factory(Subscription::class)->create()->toArray();
        $request = factory(Subscription::class)->make()->toArray();
        $response = $this->basicRequest('PATCH', '/api/v0/subscriptions/'.$subscription['pid'], $request);
        $response->assertResponseStatus(200);
        $object = json_decode($response->response->getContent());
        $newSubscription = Subscription::where('pid', $object->pid)->first();
        $response->assertTrue(isset($newSubscription));
        // need to compare discounts then unset it because of how laravel encodes json is slightly different then how phpunit encodes json
        $response->assertTrue($newSubscription->discounts == $request['discounts']);
        $response->seeJson($request);
        unset($request['discounts']);
        unset($subscription['discounts']);
        $this->seeInDatabase('autoship_subscriptions', $request);
        $this->notSeeInDatabase('autoship_subscriptions', $subscription);
        $response->seeJson($request);
    }

    public function testFind()
    {
        $subscription = factory(Subscription::class)->create()->toArray();
        $request = [
            'expands' => [
                'attempts',
                'cycle_attempts',
                'last_attempt',
                'lines',
            ],
        ];
        $response = $this->basicRequest('GET', '/api/v0/subscriptions/'.$subscription['pid'], $request);
        $response->assertResponseStatus(200);
        $response->seeJson($subscription);
    }

    public function testIndex()
    {
        $subscription = factory(Subscription::class)->create()->toArray();
        $request = [
            'expands' => [
                'attempts',
                'cycle_attempts',
                'last_attempt',
                'lines',
            ],
            'search_term' => $subscription['pid'],
            'sort_by' => '-relevance',
            'per_page' => 10
        ];
        $response = $this->basicRequest('GET', '/api/v0/subscriptions', $request);
        $response->assertResponseStatus(200);
        $response->seeJson($subscription);
    }

    public function testDelete()
    {
        $subscription = factory(Subscription::class)->create()->toArray();
        $response = $this->basicRequest('DELETE', '/api/v0/subscriptions/'.$subscription['pid']);
        $response->assertResponseStatus(200);
        unset($subscription['discounts']);
        $this->notSeeInDatabase('autoship_subscriptions', array_merge([], ['pid' => $subscription['pid'], 'deleted_at' => null]));
    }

    public function testProcess()
    {
        $subscription = factory(Subscription::class)->create(['next_billing_at' => Carbon::now()->subDay()])->toArray();
        $response = $this->basicRequest('POST', '/api/v0/subscriptions/process/'.$subscription['pid']);
        $response->assertResponseStatus(200);
        unset($subscription['next_billing_at']);
        unset($subscription['updated_at']);
        $response->seeJson($subscription);
    }
}
