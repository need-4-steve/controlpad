<?php

use App\Plan;

class PlanFailureTest extends TestCase
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

    public function testPlanUpdateWithNoPrice()
    {
        $plan = factory(Plan::class)->create();
        $updatedPlan = factory(Plan::class)->make();
        unset($updatedPlan['plan_price']);
        $response = $this->basicRequest('PATCH', '/api/v0/plans/'.$plan->pid, $updatedPlan->toArray());
        $response->assertResponseStatus(200);
        $response->seeJson($updatedPlan->toArray());
    }
}
