<?php

use Illuminate\Database\Seeder;
use App\Models\OauthDriver;

class UpdateSocialiteDriversTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OauthDriver::create([
            'name'    => 'YouTube',
            'keyname' => 'youtube'
        ]);
    }
}
