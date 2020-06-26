<?php

use Illuminate\Database\Seeder;
use App\Models\Media;
use App\Models\User;

class DefaultUsersProfileImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $resellerProfileImage = Media::create([
            'type' => 'Image',
            'filename' => '275669d2ad52894b794220d93d55d108.png',
            'url' => 'https://controlpad-hub.s3-us-west-2.amazonaws.com/cp_f0935e4cd5920aa6c7c996a5ee53a70f/275669d2ad52894b794220d93d55d108.png',
            'url_xl' => 'https://controlpad-hub.s3-us-west-2.amazonaws.com/cp_f0935e4cd5920aa6c7c996a5ee53a70f/275669d2ad52894b794220d93d55d108-url_xl.png',
            'url_lg' => 'https://controlpad-hub.s3-us-west-2.amazonaws.com/cp_f0935e4cd5920aa6c7c996a5ee53a70f/275669d2ad52894b794220d93d55d108-url_lg.png',
            'url_md' => 'https://controlpad-hub.s3-us-west-2.amazonaws.com/cp_f0935e4cd5920aa6c7c996a5ee53a70f/275669d2ad52894b794220d93d55d108-url_md.png',
            'url_sm' => 'https://controlpad-hub.s3-us-west-2.amazonaws.com/cp_f0935e4cd5920aa6c7c996a5ee53a70f/275669d2ad52894b794220d93d55d108-url_sm.png',
            'url_xs' => 'https://controlpad-hub.s3-us-west-2.amazonaws.com/cp_f0935e4cd5920aa6c7c996a5ee53a70f/275669d2ad52894b794220d93d55d108-url_xs.png',
            'url_xxs' => 'https://controlpad-hub.s3-us-west-2.amazonaws.com/cp_f0935e4cd5920aa6c7c996a5ee53a70f/275669d2ad52894b794220d93d55d108-url_xxs.png',
            'title' => 'Profile Image',
            'height' => '183',
            'width' => '183',
            'size' => '54086',
            'extension' =>'png',
            'user_id' => '106',
        ]);
        $reseller = User::find(106);
        $reseller->profileImage()->attach($resellerProfileImage);

        $affiliateProfileImage = Media::create([
            'type' => 'Image',
            'filename' => 'b3c306fc6e7c665264f1d7fc13747851.png',
            'url' => 'https://controlpad-hub.s3-us-west-2.amazonaws.com/cp_a97da629b098b75c294dffdc3e463904/aa1bfe758bfa1cfee0c213ca46283bee.jpg',
            'url_xl' => 'https://controlpad-hub.s3-us-west-2.amazonaws.com/cp_a97da629b098b75c294dffdc3e463904/aa1bfe758bfa1cfee0c213ca46283bee-url_xl.jpg',
            'url_lg' => 'https://controlpad-hub.s3-us-west-2.amazonaws.com/cp_a97da629b098b75c294dffdc3e463904/aa1bfe758bfa1cfee0c213ca46283bee-url_lg.jpg',
            'url_md' => 'https://controlpad-hub.s3-us-west-2.amazonaws.com/cp_a97da629b098b75c294dffdc3e463904/aa1bfe758bfa1cfee0c213ca46283bee-url_md.jpg',
            'url_sm' => 'https://controlpad-hub.s3-us-west-2.amazonaws.com/cp_a97da629b098b75c294dffdc3e463904/aa1bfe758bfa1cfee0c213ca46283bee-url_sm.jpg',
            'url_xs' => 'https://controlpad-hub.s3-us-west-2.amazonaws.com/cp_a97da629b098b75c294dffdc3e463904/aa1bfe758bfa1cfee0c213ca46283bee-url_xs.jpg',
            'url_xxs' => 'https://controlpad-hub.s3-us-west-2.amazonaws.com/cp_a97da629b098b75c294dffdc3e463904/aa1bfe758bfa1cfee0c213ca46283bee-url_xxs.jpg',
            'title' => 'Profile Image',
            'height' => '190',
            'width' => '190',
            'size' => '25046',
            'extension' =>'jpg',
            'user_id' => '107',
        ]);
        $affiliate = User::find(107);
        $affiliate->profileImage()->attach($affiliateProfileImage);
    }
}
