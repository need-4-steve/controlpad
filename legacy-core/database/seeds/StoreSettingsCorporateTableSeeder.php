<?php

use Illuminate\Database\Seeder;
use App\Models\StoreSettingsKey;
use App\Models\StoreSetting;
use App\Models\User;

class StoreSettingsCorporateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        $storeSettingKeys = StoreSettingsKey::all();
        $settings = [
            /*****************************
            STOREFRONT SETTINGS
            *****************************/
            // key_id = 1 store_slogan
            'We help you achieve your dreams.',
            // key_id = 2 banner_image_1
            'https://s3-us-west-2.amazonaws.com/controlpad-hub/cp_a3c65c2974270fd093ee8a9bf8ae7d0b/6b242ae3b0b66afe96e4e5cdea05ddc8-url_xl.jpg',
            // key_id = 3 banner_image_2
            'https://s3-us-west-2.amazonaws.com/controlpad-hub/cp_a3c65c2974270fd093ee8a9bf8ae7d0b/cae1ab9ccfbfa360825a58d24d25ba56-url_md.jpg',
            // key_id = 4 banner_image_3
            'https://s3-us-west-2.amazonaws.com/controlpad-hub/cp_a3c65c2974270fd093ee8a9bf8ae7d0b/5032446a909806814bfed6e796b2fa88-url_md.jpg',
            // key_id = 5 banner_text_title
            'The Story Of ' . config('site.company_name'),
            // key_id = 6 banner_text_subtitle
            'This is our story and why ' . config('site.company_name') . ' rocks!',
            // key_id = 7 banner_text_caption
            'Wearing the Chelsea Sky Top',
            // key_id = 8 category_text_1
            'OUR PICKS',
            // key_id = 9 category_text_2
            'CHELSEA SKY',

            /*****************************
            MY STORY SETTINGS
            *****************************/
            // key_id = 10 story_profile_image
            'https://s3-us-west-2.amazonaws.com/controlpad-hub/cp_a3c65c2974270fd093ee8a9bf8ae7d0b/c58d131a4caa7835270ba29d57eba2e9-url_xl.jpg',
            // key_id = 11 story_grid_image_1
            'https://s3-us-west-2.amazonaws.com/controlpad-hub/cp_a3c65c2974270fd093ee8a9bf8ae7d0b/9e1dee43299d1b25d2e4db62de8b117a-url_xl.jpg',
            // key_id = 12 story_grid_image_2
            'https://s3-us-west-2.amazonaws.com/controlpad-hub/cp_a3c65c2974270fd093ee8a9bf8ae7d0b/eeb23a0ab4746e4c6f09b575e352b583-url_xl.jpg',
            // key_id = 13 story_grid_image_3
            'https://s3-us-west-2.amazonaws.com/controlpad-hub/cp_a3c65c2974270fd093ee8a9bf8ae7d0b/b7fc632bfed2e18ed0c06f44f67a1d76-url_xl.jpg',
            // key_id = 14 story_grid_image_4
            'https://s3-us-west-2.amazonaws.com/controlpad-hub/cp_a3c65c2974270fd093ee8a9bf8ae7d0b/48bbecc34dc102ec6e9dcd047c0bf470-url_xl.jpg',
            // key_id = 15 story_title
            'OUR STORY',
            // key_id = 16 story_heading_1
            'OUR COMPANY',
            // key_id = 17 story_heading_2
            'WHAT WE DO',
            // key_id = 18 story_heading_3
            'HOW WE DO IT',
            // key_id = 19 story_heading_4
            'WHY WE DO IT',
            // key_id = 20 story_text_1
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec non pharetra purus, ac efficitur ante. Pellentesque sed sem feugiat, congue mauris ut, convallis sem. Curabitur nibh urna, molestie et ultrices quis, sollicitudin vitae massa. Donec tempor ipsum at magna tincidunt sollicitudin. Aliquam pretium urna nulla, nec bibendum quam auctor vitae. Etiam porta scelerisque ultricies. Phasellus et sem in leo efficitur cursus sed id massa. Interdum et malesuada fames ac ante ipsum primis in faucibus. Curabitur interdum felis velit, quis volutpat ipsum vulputate vitae. Nulla vehicula nec lectus eget eleifend. Praesent auctor efficitur orci at scelerisque. Donec et lobortis nibh, sit amet laoreet sem. Nunc non nunc vitae leo efficitur vestibulum at nec lectus. Phasellus vel dolor vitae leo consequat fermentum. In a arcu condimentum, semper ligula et, placerat sapien.',
            // key_id = 21 story_text_2
            'Maecenas vestibulum est quis ante sollicitudin molestie. Mauris lorem nunc, bibendum et turpis non, laoreet iaculis tellus. Donec aliquet, nibh eget ultricies vulputate, justo risus mollis lorem, gravida elementum lectus enim et erat. Nullam et volutpat elit. Etiam mattis justo congue libero dapibus mollis. Nam non nibh lorem. Ut bibendum felis eget tincidunt tempus. Proin vitae ante finibus, euismod sapien a, iaculis mauris. Pellentesque malesuada ligula vel tellus pulvinar, vitae egestas sapien accumsan. In pretium nibh eget orci sollicitudin fringilla. Suspendisse iaculis pulvinar est, ut luctus tortor rhoncus non. Pellentesque sed faucibus diam. Fusce ultricies, magna ut vestibulum finibus, lorem sem blandit odio, gravida mollis elit velit finibus tellus. Curabitur sit amet aliquet orci, non semper ex. Morbi commodo fermentum sem eget tristique.',
            // key_id = 22 story_text_3
            'Suspendisse ultrices pellentesque ligula eget luctus. Morbi at consectetur ex, in placerat orci. Mauris ac massa non tortor consequat luctus at id odio. Donec arcu ligula, aliquet nec vulputate a, rhoncus ac ex. Nulla in eleifend turpis. Quisque condimentum quam eu ipsum fringilla dapibus. In hac habitasse platea dictumst. Duis vestibulum mi neque, non volutpat sem venenatis ac.',
            // key_id = 23 story_text_4
            'Ut fermentum eros eget semper molestie. Integer bibendum mauris a diam mollis, nec vulputate tellus dictum. Sed ornare lobortis pellentesque. Pellentesque non mollis nibh, non facilisis dolor. Nullam sit amet posuere sapien, vel bibendum turpis. Nunc convallis purus at justo convallis, ac hendrerit urna fringilla. Cras sed ante condimentum, tempor mi eget, rhoncus odio. Pellentesque a magna magna.',
            // key_id = 24 story_intro
            'Nulla bibendum egestas eros ac dictum. Suspendisse potenti. Quisque enim velit, rutrum eget ligula eget, facilisis tincidunt lorem. Aliquam condimentum nec dui gravida accumsan. Aliquam rutrum ligula quis tortor pretium aliquet. Donec tristique tellus ac elementum semper. Maecenas vulputate orci eros, at fermentum enim tempor et. Suspendisse lectus purus, sollicitudin id sem in, pulvinar auctor nulla. Etiam rhoncus justo non accumsan semper. Phasellus elit magna, aliquet sed porta vel, placerat eget lectus. Sed at vehicula augue. Curabitur pulvinar turpis nec metus eleifend, eget viverra turpis molestie. Fusce velit mi, fermentum in consequat in, sodales at nisl. Sed nec est eu mi tempus molestie a in urna. Suspendisse id sodales lectus, in ultricies ex.',
            // key_id = 25 set display name
            config('site.company_name'),
            // key_id = 26 fulfillment time for shipping
            'Orders ship within 2 business days',
            // key_id = 27 rep logo
            '',
            // key_id = 28 show_store_banner
            true,
            // key_id = 29 show_banner_image_1
            true,
            // key_id = 30 show_banner_image_2
            true,
            // key_id = 31 show_banner_image_1
            true,
            // key_id = 32 about_me
            '{"image_url":"","title":"","body":"","facebook_url":"","instagram_url":""}'
        ];
        $user = User::where('id', config('site.apex_user_id'))->first();
        foreach ($storeSettingKeys as $key => $storeSettingKey) {
            $storeSetting = new StoreSetting();
            $storeSetting->user_id = $user->id;
            $storeSetting->user_pid = $user->pid;
            $storeSetting->key_id = $storeSettingKey->id;
            $storeSetting->key = $storeSettingKey->key;
            $storeSetting->value = $settings[$key];
            $storeSetting->save();
        }
        DB::commit();
    }
}
