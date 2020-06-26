<?php

use App\Models\User;
use App\Models\Role;
use App\Services\Commission;

class FakeUsersTableSeeder extends DatabaseSeeder
{
    public function run()
    {
        DB::update("ALTER TABLE users AUTO_INCREMENT = 2000;");
        DB::beginTransaction();
        // founding users
        $users = factory(User::class, 'rep', 5)->create([
            'sponsor_id' => config('site.apex_user_id')
        ]);

        // nested users
        factory(User::class, 'rep', 10)->create();
        DB::commit();
    }
}
