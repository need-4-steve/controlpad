<?php

use App\Plan;

class PlanTest extends TestCase
{
    protected static $planStructure = [
        'description',
        'duration',
        'free_trial_time',
        'on_sign_up',
        'renewable',
        'seller_type_id',
        'tax_class',
        'title',
        'plan_price',
    ];

    public function testPlanIndex()
    {
        $plan = factory(Plan::class)->create();
        $response = $this->basicRequest('GET', '/api/v0/plans/');
        $response->assertResponseStatus(200);
        $response->seeJsonStructure(['data' =>
            [
                '*' => $this::$planStructure
            ]
        ]);
        $response->seeJson($plan->toArray());
    }

    public function testPlanIndexOnSignUp()
    {
        $plan = factory(Plan::class)->create(['on_sign_up' => 0]);
        $response = $this->basicRequest('GET', '/api/v0/plans/', ['sign_up' => true]);
        $response->assertResponseStatus(200);
        $response->dontSeeJson(['pid' => $plan->pid]);
    }

    public function testPlanCreate()
    {
        $plan = factory(Plan::class)->make();
        $response = $this->basicRequest('POST', '/api/v0/plans/', $plan->toArray());
        $response->assertResponseStatus(200);
        $response->seeJson($plan->toArray());
    }

    public function testPlanUpdate()
    {
        $plan = factory(Plan::class)->create();
        $updatedPlan = factory(Plan::class)->make();
        $response = $this->basicRequest('PATCH', '/api/v0/plans/'.$plan->pid, $updatedPlan->toArray());
        $response->assertResponseStatus(200);
        $response->seeJson($updatedPlan->toArray());
    }

    public function testPlanDelete()
    {
        $plan = factory(Plan::class)->create();
        $response = $this->basicRequest('DELETE', '/api/v0/plans/'.$plan->pid);
        $response->assertResponseStatus(200);
        $this->notSeeInDatabase('subscriptions', [
            'pid' => $plan->pid,
        ]);
    }
}
