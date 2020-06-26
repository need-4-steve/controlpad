<?php

use App\Models\Subscription;

class SubscriptionFailureTest extends TestCase
{
    public function testCreateRequired()
    {
        $fields = [
            'buyer_id',
            'discount',
            'duration',
            'free_shipping',
            'frequency',
            'next_billing_at',
            'order_type_id',
            'order_type',
            'seller_id',
        ];
        foreach ($fields as $key => $field) {
            $request = factory(Subscription::class)->make()->toArray();
            unset($request[$field]);
            $response = $this->basicRequest('POST', '/api/v0/subscriptions', $request);
            $response->assertResponseStatus(422);
        }
    }

    public function testCreateFailure()
    {
        $fields = [
            'buyer_id'              => 'error',
            'discount'              => 'error',
            'disable'               => 'error',
            'duration'              => 'error',
            'free_shipping'         => 'error',
            'frequency'             => 'error',
            'next_billing_at'       => 'error',
            'order_type_id'         => 'error',
            'order_type'            => 'error',
            'plan_pid'              => 'error',
            'seller_id'             => 'error',
        ];
        foreach ($fields as $key => $field) {
            $request = factory(Subscription::class)->make()->toArray();
            $request[$key] = $field;
            $response = $this->basicRequest('POST', '/api/v0/subscriptions', $request);
            $response->assertResponseStatus(422);
        }
    }

    public function testCreateNull()
    {
        $fields = [
            'buyer_id'              => null,
            'disable'               => null,
            'duration'              => null,
            'free_shipping'         => null,
            'frequency'             => null,
            'next_billing_at'       => null,
            'order_type_id'         => null,
            'order_type'            => null,
            'seller_id'             => null,
        ];
        foreach ($fields as $key => $field) {
            $request = factory(Subscription::class)->make()->toArray();
            $request[$key] = $field;
            $response = $this->basicRequest('POST', '/api/v0/subscriptions', $request);
            $response->assertResponseStatus(422);
        }
    }

    public function testUpdateFailure()
    {
        $fields = [
            'buyer_id'              => 'error',
            'discount'              => 'error',
            'disable'               => 'error',
            'duration'              => 'error',
            'free_shipping'         => 'error',
            'frequency'             => 'error',
            'next_billing_at'       => 'error',
            'order_type_id'         => 'error',
            'order_type'            => 'error',
            'plan_pid'              => 'error',
            'seller_id'             => 'error',
        ];
        foreach ($fields as $key => $field) {
            $request = factory(Subscription::class)->make()->toArray();
            $request[$key] = $field;
            $response = $this->basicRequest('POST', '/api/v0/subscriptions', $request);
            $response->assertResponseStatus(422);
        }
    }

    public function testUpdateNull()
    {
        $fields = [
            'disable'               => null,
            'duration'              => null,
            'free_shipping'         => null,
            'frequency'             => null,
            'next_billing_at'       => null,
        ];
        foreach ($fields as $key => $field) {
            $subscription = factory(Subscription::class)->create()->toArray();
            $request = factory(Subscription::class)->make()->toArray();
            $request[$key] = $field;
            $response = $this->basicRequest('PATCH', '/api/v0/subscriptions/'.$subscription['pid'], $request);
            $response->assertResponseStatus(422);
        }
    }

    public function testIndexFailure()
    {
        $fields = [
            'search_term' => 3,
            'sort_by' => 'error',
            'sort_by' => 'error',
            'per_page' => 'error',
        ];
        foreach ($fields as $key => $field) {
            $request = [];
            $request[$key] = $field;
            $response = $this->basicRequest('GET', '/api/v0/subscriptions/', $request);
            $response->assertResponseStatus(422);
        }
    }

    public function testIndexNull()
    {
        $fields = [
            'search_term' => null,
            'sort_by' => null,
            'per_page' => null,
        ];
        foreach ($fields as $key => $field) {
            $request = [];
            $request[$key] = $field;
            $response = $this->basicRequest('GET', '/api/v0/subscriptions/', $request);
            $response->assertResponseStatus(422);
        }
    }

    public function testDelete()
    {
        $response = $this->basicRequest('DELETE', '/api/v0/subscriptions/error');
        $response->assertResponseStatus(422);
    }
}
