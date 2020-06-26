<?php

use App\Plan;
use Carbon\Carbon;

$factory->define(App\Subscription::class, function ($faker) {
    $plan = factory(Plan::class)->create();
    return [
        'user_id' => 0,
        'user_pid' => '',
        'subscription_id' => $plan->id,
        'subscription_price' => $plan->plan_price,
        'ends_at' => Carbon::now('UTC')->addMonths(1)->toDateTimeString(),
        'auto_renew' => 0
    ];
});
