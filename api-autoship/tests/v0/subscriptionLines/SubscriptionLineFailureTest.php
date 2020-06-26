<?php

use App\Models\Subscription;
use App\Models\SubscriptionLine;

class SubscriptionLineFailureTest extends TestCase
{
    public function testCreateRequired()
    {
        $fields = [
            'item_id',
            'price',
            'quantity',
            'subscription_pid',
        ];
        foreach ($fields as $key => $field) {
            $request = factory(SubscriptionLine::class)->make()->toArray();
            unset($request[$field]);
            $response = $this->basicRequest('POST', '/api/v0/subscription-lines', $request);
            $response->assertResponseStatus(422);
        }
    }

    public function testCreateFailure()
    {
        $fields = [
            'item_id'                   => 'error',
            'price'                     => 'error',
            'quantity'                  => 'error',
            'subscription_pid'          => 'error',
            'disable'                   => 'error',
        ];
        foreach ($fields as $key => $field) {
            $request = factory(SubscriptionLine::class)->make()->toArray();
            $subscription = Subscription::orderBy('created_at', 'desc')->first();
            $request['subscription_pid'] = $subscription->pid;
            $request[$key] = $field;
            $response = $this->basicRequest('POST', '/api/v0/subscription-lines', $request);
            $response->assertResponseStatus(422);
        }
    }

    public function testCreateNull()
    {
        $fields = [
            'item_id'                   => null,
            'price'                     => null,
            'quantity'                  => null,
            'subscription_pid'          => null,
            'disable'                   => null,
        ];
        foreach ($fields as $key => $field) {
            $request = factory(SubscriptionLine::class)->make()->toArray();
            $subscription = Subscription::orderBy('created_at', 'desc')->first();
            $request['subscription_pid'] = $subscription->pid;
            $request[$key] = $field;
            $response = $this->basicRequest('POST', '/api/v0/subscription-lines', $request);
            $response->assertResponseStatus(422);
        }
    }

    public function testUpdateFailure()
    {
        $fields = [
            'item_id'                   => 'error',
            'price'                     => 'error',
            'quantity'                  => 'error',
            'subscription_pid'          => 'error',
            'disable'                   => 'error',
        ];
        foreach ($fields as $key => $field) {
            $subscriptionLine = factory(SubscriptionLine::class)->create()->toArray();
            $request = factory(SubscriptionLine::class)->make()->toArray();
            $request[$key] = $field;
            $response = $this->basicRequest('PATCH', '/api/v0/subscription-lines/'.$subscriptionLine['pid'], $request);
            $response->assertResponseStatus(422);
        }
    }

    public function testDelete()
    {
        $response = $this->basicRequest('DELETE', '/api/v0/subscription-lines/error');
        $response->assertResponseStatus(422);
    }
}
