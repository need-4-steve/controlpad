<?php

use App\Models\Plan;

class PlanFailureTest extends TestCase
{
    public function testCreateRequired()
    {
        $fields = [
            'duration',
            'frequency',
            'discounts',
        ];
        foreach ($fields as $key => $field) {
            $request = factory(Plan::class)->make()->toArray();
            unset($request[$field]);
            $response = $this->basicRequest('POST', '/api/v0/plans', $request);
            $response->assertResponseStatus(422);
        }
    }

    public function testCreateFailure()
    {
        $fields = [
            'description' => 3,
            'disable' => 'error',
            'discounts' => 'error',
            'duration' => 'error',
            'free_shipping' => 'error',
            'frequency' => 'error',
            'title' => 3
        ];
        foreach ($fields as $key => $field) {
            $request = factory(Plan::class)->make()->toArray();
            $request[$key] = $field;
            $response = $this->basicRequest('POST', '/api/v0/plans', $request);
            $response->assertResponseStatus(422);
        }
    }

    public function testCreateNull()
    {
        $fields = [
            'disable' => null,
            'discounts' => null,
            'duration' => null,
            'free_shipping' => null,
            'frequency' => null,
        ];
        foreach ($fields as $key => $field) {
            $request = factory(Plan::class)->make()->toArray();
            $request[$key] = $field;
            $response = $this->basicRequest('POST', '/api/v0/plans', $request);
            $response->assertResponseStatus(422);
        }
    }

    public function testUpdateFailure()
    {
        $fields = [
            'description' => 3,
            'disable' => 'error',
            'discounts' => 'error',
            'duration' => 'error',
            'free_shipping' => 'error',
            'frequency' => 'error',
            'title' => 3
        ];
        foreach ($fields as $key => $field) {
            $plan = factory(Plan::class)->create()->toArray();
            $request = factory(Plan::class)->make()->toArray();
            $request[$key] = $field;
            $response = $this->basicRequest('PATCH', '/api/v0/plans/'.$plan['pid'], $request);
            $response->assertResponseStatus(422);
        }
    }

    public function testUpdateNull()
    {
        $fields = [
            'disable' => null,
            'discounts' => null,
            'duration' => null,
            'free_shipping' => null,
            'frequency' => null,
        ];
        foreach ($fields as $key => $field) {
            $plan = factory(Plan::class)->create()->toArray();
            $request = factory(Plan::class)->make()->toArray();
            $request[$key] = $field;
            $response = $this->basicRequest('PATCH', '/api/v0/plans/'.$plan['pid'], $request);
            $response->assertResponseStatus(422);
        }
    }

    public function testIndexFailure()
    {
        $fields = [
            'search_term' => 3,
            'sort_by' => 'error',
            'sort_by' => 'relevance',
            'per_page' => 'error',
            'visibilities' => 'error',
        ];
        foreach ($fields as $key => $field) {
            $request = [];
            $request[$key] = $field;
            $response = $this->basicRequest('GET', '/api/v0/plans/', $request);
            $response->assertResponseStatus(422);
        }
    }

    public function testIndexNull()
    {
        $fields = [
            'search_term' => null,
            'sort_by' => null,
            'per_page' => null,
            'visibilities' => null,
        ];
        foreach ($fields as $key => $field) {
            $request = [];
            $request[$key] = $field;
            $response = $this->basicRequest('GET', '/api/v0/plans/', $request);
            $response->assertResponseStatus(422);
        }
    }

    public function testDelete()
    {
        $response = $this->basicRequest('DELETE', '/api/v0/plans/error');
        $response->assertResponseStatus(422);
    }
}
