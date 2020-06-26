<?php

use Illuminate\Database\Seeder;
use App\Models\SubscriptionUser;
use \Carbon\Carbon;

class fixSubscriptionChalkJuly extends Seeder
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
        $date = Carbon::createFromDate(2017, 7, 15, 'UTC')->EndOfDay();
        $date = $date->addHours(6)->toDateTimeString();
        foreach ($userSubscriptions as $user) {
            if ($user->created_at <= $date) {
                $days =$user->created_at->diffInDays($user->ends_at);
                if ($days <= 60) {
                    $user->ends_at = $user->ends_at->addDays(30);
                    $user->save();
                }
            }
        }
        DB::commit();
    }
}
