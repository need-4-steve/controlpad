<?php

use App\Models\Phone;
use App\Models\User;

class PhonesTableSeeder extends DatabaseSeeder
{

    public function run()
    {
        DB::beginTransaction();
        $users = User::all();

        foreach ($users as $user) {
            $phone = factory(Phone::class, 1)->create([
                'phonable_id' => $user->id
            ]);
            $user->phone_number = $phone->number;
            $user->save();
        }
        DB::commit();
    }
}
