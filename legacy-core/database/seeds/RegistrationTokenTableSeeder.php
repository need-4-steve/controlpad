<?php

use Illuminate\Database\Seeder;

use App\Models\RegistrationToken;

class RegistrationTokenTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        RegistrationToken::create([
            'token' => 'TEST',
            'source_id' => 1,
            'first_name' => 'Finn',
            'last_name' => 'Human',
            'user_id' => 0,
            'email' => 'Finn@human.com'
        ]);
        DB::commit();
    }
}
