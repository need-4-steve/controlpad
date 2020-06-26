<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Models\StoreSetting;
use App\Models\StoreSettingsKey;
use App\Models\User;
use Carbon\Carbon;
use DB;

class StoreSettingRepository
{
    /**
     * Get users settings for their store
     *
     * @param  Integer: user id
     * @return Array
     */
    public function getSettingsByUser($user_id)
    {
        $storeSettings = StoreSetting::where('user_id', $user_id)->get();
        return array_reduce($storeSettings->toArray(), function (&$result, $item) {
                $result[$item['key']] = $item['value'];
                return $result;
        });
    }

    /**
     * retrieve display name by user
     *
     * @param int $user_id
     * @return String
     */
    public function getDisplayName($user_id)
    {
        $setting = StoreSetting::where('user_id', $user_id)->where('key_id', 25)->first();
        return $setting->value;
    }

    /**
     * Updates a category header or attaches a new one to a user.
     *
     * @param User $user
     * @param Integer $category
     * @param String $header
     * @return Category $category
     */
    public function updateCategoryHeader(User $user, $category_id, $header)
    {
        $category = $user->categories()->where('category_id', $category_id)->first();

        if (!$category) {
            $category = Category::where('id', $category_id)->first();
        } else {
            $category->pivot->update(['header' => $header]);
            return $category;
        }

        if (!$category) {
            return ['error' => 'Could not find Category'];
        }

        $user->categories()->attach($category->id, ['header' => $header]);

        return $user->categories()->where('category_id', $category->id)->first();
    }

    /**
     * Updates a store setting or creates a new one.
     *
     * @param User $user
     * @param String $key
     * @param String $value
     * @return StoreSetting $setting
     */
    public function update(User $user, $key, $value)
    {
        $keySetting = StoreSettingsKey::where('key', $key)->first();
        if (!isset($keySetting)) {
            return ['error' => 'Could not find setting for '.$key];
        }
        $setting = $user->storeSettings()->firstOrNew(['key_id' => $keySetting->id]);
        $setting->key_id = $keySetting->id;
        $setting->value = $value;
        $setting->save();
        return $setting;
    }

    /**
     * Gets a store categories settings.
     *
     * @param Integer $userId
     * @return Category $categories
     */
    public function getCategories(int $userId)
    {
        $user = User::find($userId);
        if ($user->hasSellerType(['Affiliate'])) {
            $user = User::find(config('site.apex_user_id'));
        }
        $userCategories = $user->categories()->get();
        $categories = Category::where('parent_id', null)
            ->where('show_on_store', '=', 1)
            ->whereHas('product.items.inventory', function ($query) use ($user) {
                $query->inStock($user->id);
            })
            ->with('media', 'children')
            ->get();

        foreach ($categories as $category) {
            $category->header = array_get($userCategories->where('id', $category->id)->first(), 'pivot.header');
        }

        return $categories;
    }

    public function createSettings($userId, $fullName)
    {
        $now = Carbon::now('UTC');
        $user = User::where('id', $userId)->first();
        StoreSetting::insert([
            ['key_id' => 1,  'key' => 'store_slogan',                'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'living my dream of being a stay at home CEO'],
            ['key_id' => 2,  'key' => 'banner_image_1',              'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'https://s3-us-west-2.amazonaws.com/controlpad/home-blog-main.jpg'],
            ['key_id' => 3,  'key' => 'banner_image_2',              'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/f8d8153fb4f29d3af15276db22435d48-url_md.jpg'],
            ['key_id' => 4,  'key' => 'banner_image_3',              'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/ac77a946706c680ac33c4a5036e3d810-url_md.jpg'],
            ['key_id' => 5,  'key' => 'banner_text_title',           'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'My Story'],
            ['key_id' => 6,  'key' => 'banner_text_subtitle',        'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'The incredible story of a Rockstar!'],
            ['key_id' => 7,  'key' => 'banner_text_caption',         'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'wearing the Chelsea Sky Top'],
            ['key_id' => 8,  'key' => 'category_text_1',             'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'My picks for this week'],
            ['key_id' => 9,  'key' => 'category_text_2',             'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'Chelsea Sky Collection'],
            ['key_id' => 10, 'key' => 'story_profile_image',         'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'https://s3-us-west-2.amazonaws.com/controlpad/BlondePortrait.jpg'],
            ['key_id' => 11, 'key' => 'story_grid_image_1',          'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'https://s3-us-west-2.amazonaws.com/controlpad/pexels-photo-108070.jpeg'],
            ['key_id' => 12, 'key' => 'story_grid_image_2',          'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'https://s3-us-west-2.amazonaws.com/controlpad/hands-people-woman-working.jpg'],
            ['key_id' => 13, 'key' => 'story_grid_image_3',          'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'https://s3-us-west-2.amazonaws.com/controlpad/pexels-photo.jpg'],
            ['key_id' => 14, 'key' => 'story_grid_image_4',          'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'https://s3-us-west-2.amazonaws.com/controlpad/pexels-photo-128899.jpeg'],
            ['key_id' => 15, 'key' => 'story_title',                 'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'WHO AM I?'],
            ['key_id' => 16, 'key' => 'story_heading_1',             'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'Family'],
            ['key_id' => 17, 'key' => 'story_heading_2',             'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'Occupation'],
            ['key_id' => 18, 'key' => 'story_heading_3',             'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'Recreation'],
            ['key_id' => 19, 'key' => 'story_heading_4',             'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'Motivation'],
            ['key_id' => 20, 'key' => 'story_text_1',                'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In tincidunt iaculis tellus ac porttitor. In et turpis facilisis, maximus risus molestie, maximus elit.'],
            ['key_id' => 21, 'key' => 'story_text_2',                'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In tincidunt iaculis tellus ac porttitor. In et turpis facilisis, maximus risus molestie, maximus elit.'],
            ['key_id' => 22, 'key' => 'story_text_3',                'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In tincidunt iaculis tellus ac porttitor. In et turpis facilisis, maximus risus molestie, maximus elit.'],
            ['key_id' => 23, 'key' => 'story_text_4',                'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In tincidunt iaculis tellus ac porttitor. In et turpis facilisis, maximus risus molestie, maximus elit.'],
            ['key_id' => 24, 'key' => 'story_intro',                 'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In tincidunt iaculis tellus ac porttitor. In et turpis facilisis, maximus risus molestie, maximus elit.'],
            ['key_id' => 25, 'key' => 'display_name',                'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => $fullName],
            ['key_id' => 26, 'key' => 'shipping_fulfillment_time',   'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'Orders ship within 2 business days'],
            ['key_id' => 27, 'key' => 'logo',                        'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => ''],
            ['key_id' => 28, 'key' => 'show_store_banner',           'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => true],
            ['key_id' => 29, 'key' => 'show_banner_image_1',         'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => true],
            ['key_id' => 30, 'key' => 'show_banner_image_2',         'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => true],
            ['key_id' => 31, 'key' => 'show_banner_image_3',         'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => true],
            ['key_id' => 32, 'key' => 'about_me',                    'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => '{"image_url":"","title":"","body":"","facebook_url":"","instagram_url":""}']
        ]);
    }
}
