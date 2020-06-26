<?php

use Illuminate\Database\Seeder;
use App\Models\Attempt;
use App\Models\Subscription;
use App\Models\SubscriptionLine;
use App\Repositories\Eloquent\V0\SubscriptionRepository;

class SubscriptionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subscriptionRepo = new SubscriptionRepository();
        factory(SubscriptionLine::class, 'Seeder', 20)->create();
        $subscriptions = Subscription::all();
        DB::beginTransaction();
        foreach ($subscriptions as $subscription) {
            if (Schema::hasTable('users')) {
                $buyer = DB::table('users')
                    ->select('pid', 'first_name', 'last_name')
                    ->whereIn('role_id', [3,5])
                    ->inRandomOrder()
                    ->first();
                $subscription->buyer_pid = $buyer->pid;
                $subscription->buyer_first_name = $buyer->first_name;
                $subscription->buyer_last_name = $buyer->last_name;
                $seller = DB::table('users')
                    ->select('pid')
                    ->first();
                $subscription->seller_pid = $seller->pid;
                $subscription->inventory_user_pid = $seller->pid;
            }
            $cycle = 1;
            $failure = 0;
            $attempts = factory(Attempt::class, rand(10, 30))->create(['subscription_cycle' => $cycle, 'autoship_subscription_id' => $subscription->id]);
            foreach ($attempts as $attempt) {
                if ($attempt->status === 'success') {
                    $attempt->subscription_cycle = $cycle;
                    $attempt->save();
                    $cycle++;
                    $failure = 0;
                } elseif ($attempt->status === 'failure') {
                    $attempt->subscription_cycle = $cycle;
                    $attempt->save();
                    $failure++;
                    if ($failure >= 3) {
                        $cycle++;
                        $failure = 0;
                    }
                } else {
                    $attempt->subscription_cycle = $cycle;
                    $attempt->save();
                }
            }
            $subscription->cycle = $cycle;
            $subscription->save();
            $subscriptionRepo->determineDiscount($subscription);
        }
        DB::commit();
    }
}
