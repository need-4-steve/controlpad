<?php

use Illuminate\Database\Seeder;

use App\Models\OauthDriver;

class SocialiteDriversTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OauthDriver::create([
            'name'    => 'Facebook',
            'keyname' => 'facebook'
        ]);

        OauthDriver::create([
            'name'    => 'Instagram',
            'keyname' => 'instagram'
        ]);

        OauthDriver::create([
            'name'    => 'Gmail',
            'keyname' => 'gmail'
        ]);

        OauthDriver::create([
            'name'    => 'YouTube',
            'keyname' => 'youtube'
        ]);
    }
}
