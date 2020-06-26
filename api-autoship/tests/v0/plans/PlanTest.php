<?php

use App\Models\Plan;

class PlanTest extends TestCase
{
    public function testCreate()
    {
        $request = factory(Plan::class)->make()->toArray();
        $response = $this->basicRequest('POST', '/api/v0/plans', array_merge($request, ['visibilities' => [['id' => 5]]]));
        $response->assertResponseStatus(200);
        $object = json_decode($response->response->getContent());
        $plan = Plan::where('pid', $object->pid)->first();
        $response->assertTrue(isset($plan));
        // need to compare discounts then unset it because of how laravel encodes json is slightly different then how phpunit encodes json
        $response->assertTrue($plan->discounts == $request['discounts']);
        $response->seeJson($request);
        unset($request['discounts']);
        $this->seeInDatabase('autoship_plans', $request);
        $this->seeInDatabase('autoship_plan_visibility', ['autoship_plan_id' => $plan->id, 'visibility_id' => 5]);
    }

    public function testUpdate()
    {
        $plan = factory(Plan::class)->create()->toArray();
        $request = factory(Plan::class)->make()->toArray();
        $response = $this->basicRequest('PATCH', '/api/v0/plans/'.$plan['pid'], array_merge($request, ['visibilities' => [['id' => 5]]]));
        $response->assertResponseStatus(200);
        $updatedPlan = Plan::where('pid', $plan['pid'])->first();
        $response->assertTrue(isset($updatedPlan));
        // need to compare discounts then unset it because of how laravel encodes json is slightly different then how phpunit encodes json
        $response->assertTrue($updatedPlan->discounts == $request['discounts']);
        $response->seeJson($request);
        unset($request['discounts']);
        unset($plan['discounts']);
        $this->seeInDatabase('autoship_plans', $request);
        $this->notSeeInDatabase('autoship_plans', $plan);
        $this->seeInDatabase('autoship_plan_visibility', ['autoship_plan_id' => $updatedPlan->id, 'visibility_id' => 5]);
    }

    public function testFind()
    {
        $plan = factory(Plan::class)->create()->toArray();
        $response = $this->basicRequest('GET', '/api/v0/plans/'.$plan['pid']);
        $response->assertResponseStatus(200);
        $response->seeJson($plan);
    }

    public function testIndex()
    {
        $plan = factory(Plan::class)->create()->toArray();
        $request = [
            'search_term' => $plan['pid'],
            'show_disabled' => true,
            'sort_by' => '-relevance',
            'per_page' => '5',
        ];
        $response = $this->basicRequest('GET', '/api/v0/plans', $request);
        $response->assertResponseStatus(200);
        $response->seeJson($plan);
    }

    public function testDelete()
    {
        $plan = factory(Plan::class)->create()->toArray();
        $response = $this->basicRequest('DELETE', '/api/v0/plans/'.$plan['pid']);
        $response->assertResponseStatus(200);
        unset($plan['discounts']);
        $this->notSeeInDatabase('autoship_plans', array_merge($plan, ['deleted_at' => null]));
    }
}
