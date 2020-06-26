<?php

use Illuminate\Database\Seeder;
use App\Models\StoreSettingsKey;

class StoreSettingsKeysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();

        Schema::disableForeignKeyConstraints();
        DB::table('store_settings_keys')->truncate();
        Schema::enableForeignKeyConstraints();
        //storefront settings
        StoreSettingsKey::create(['id' => 1, 'key' => 'store_slogan']);
        StoreSettingsKey::create(['id' => 2, 'key' => 'banner_image_1']);
        StoreSettingsKey::create(['id' => 3, 'key' => 'banner_image_2']);
        StoreSettingsKey::create(['id' => 4, 'key' => 'banner_image_3']);
        StoreSettingsKey::create(['id' => 5, 'key' => 'banner_text_title']);
        StoreSettingsKey::create(['id' => 6, 'key' => 'banner_text_subtitle']);
        StoreSettingsKey::create(['id' => 7, 'key' => 'banner_text_caption']);
        StoreSettingsKey::create(['id' => 8, 'key' => 'category_text_1']);
        StoreSettingsKey::create(['id' => 9, 'key' => 'category_text_2']);
        // my story settings
        StoreSettingsKey::create(['id' => 10, 'key' => 'story_profile_image']);
        StoreSettingsKey::create(['id' => 11, 'key' => 'story_grid_image_1']);
        StoreSettingsKey::create(['id' => 12, 'key' => 'story_grid_image_2']);
        StoreSettingsKey::create(['id' => 13, 'key' => 'story_grid_image_3']);
        StoreSettingsKey::create(['id' => 14, 'key' => 'story_grid_image_4']);
        StoreSettingsKey::create(['id' => 15, 'key' => 'story_title']);
        StoreSettingsKey::create(['id' => 16, 'key' => 'story_heading_1']);
        StoreSettingsKey::create(['id' => 17, 'key' => 'story_heading_2']);
        StoreSettingsKey::create(['id' => 18, 'key' => 'story_heading_3']);
        StoreSettingsKey::create(['id' => 19, 'key' => 'story_heading_4']);
        StoreSettingsKey::create(['id' => 20, 'key' => 'story_text_1']);
        StoreSettingsKey::create(['id' => 21, 'key' => 'story_text_2']);
        StoreSettingsKey::create(['id' => 22, 'key' => 'story_text_3']);
        StoreSettingsKey::create(['id' => 23, 'key' => 'story_text_4']);
        StoreSettingsKey::create(['id' => 24, 'key' => 'story_intro']);
        StoreSettingsKey::create(['id' => 25, 'key' => 'display_name']);
        StoreSettingsKey::create(['id' => 26, 'key' => 'shipping_fulfillment_time']);
        StoreSettingsKey::create(['id' => 27, 'key' => 'logo']);
        StoreSettingsKey::create(['id' => 28, 'key' => 'show_store_banner']);
        StoreSettingsKey::create(['id' => 29, 'key' => 'show_banner_image_1']);
        StoreSettingsKey::create(['id' => 30, 'key' => 'show_banner_image_2']);
        StoreSettingsKey::create(['id' => 31, 'key' => 'show_banner_image_3']);
        StoreSettingsKey::insert(['id' => 32, 'key' => 'about_me',]);
        DB::commit();
    }
}
