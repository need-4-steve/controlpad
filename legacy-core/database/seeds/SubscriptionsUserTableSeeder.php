<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

use App\Models\SubscriptionUser;
use App\Models\User;
use App\Models\Role;

class SubscriptionsUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        $repRole = Role::where('name', 'Rep')->first();
        $users = User::where('role_id', $repRole->id)->doesntHave('subscriptions')->get();
        foreach ($users as $user) {
            SubscriptionUser::create([
                'subscription_id' => 1,
                'subscription_price' => 24.95,
                'user_id'         => $user->id,
                'user_pid'        => $user->pid,
                'auto_renew'      => true,
                'ends_at'         => Carbon::now()->addMonth()->toDateTimeString(),
            ]);
        }
        DB::commit();
    }
}
