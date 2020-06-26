<?php

use Illuminate\Database\Seeder;

use App\Models\UserSetting;
use App\Models\User;
use App\Models\Role;

class UserSettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();

        // grab which is the customer role to filter out customers
        $customerRole = Role::where('name', 'Customer')->first();
        $users = User::where('role_id', '!=', $customerRole->id)->whereDoesntHave('settings')->get();
        
        foreach ($users as $user) {
            $userSettings = factory(UserSetting::class, 1)->make([
                    'user_id' => $user->id,
                    'user_pid' => $user->pid,
            ])->toArray();
            UserSetting::create($userSettings);
        }
        DB::commit();
    }
}
