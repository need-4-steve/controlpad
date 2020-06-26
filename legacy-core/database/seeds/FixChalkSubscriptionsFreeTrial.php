<?php

use Illuminate\Database\Seeder;
use App\Models\SubscriptionUser;
use \Carbon\Carbon;

class FixChalkSubscriptionsFreeTrial extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        $userSubscriptions = SubscriptionUser::with('subscription')->get();
        foreach ($userSubscriptions as $user) {
                $user->ends_at = $user->ends_at->addDays(30);
                $user->save();
        }
        DB::commit();
    }
}
