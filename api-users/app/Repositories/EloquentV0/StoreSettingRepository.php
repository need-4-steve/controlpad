<?php

namespace App\Repositories\EloquentV0;

use App\StoreSetting;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use App\User;
use Carbon\Carbon;

class StoreSettingRepository extends Repository
{
    public function create(User $user)
    {
        $now = Carbon::now('UTC');
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
            ['key_id' => 25, 'key' => 'display_name',                'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => $user->first_name.' '.$user->last_name],
            ['key_id' => 26, 'key' => 'shipping_fulfillment_time',   'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => 'Orders ship within 2 business days'],
            ['key_id' => 27, 'key' => 'logo',                        'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => ''],
            ['key_id' => 28, 'key' => 'show_store_banner',           'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => true],
        ]);
    }

    public function find($userPid, $key)
    {
        $setting = StoreSetting::where('user_pid', $userPid)->where('key', $key)->first();
        if (isset($setting->value)) {
            return $setting->value;
        }
        return null;
    }

    public function index($userPid)
    {
        $settings = StoreSetting::where('user_pid', $userPid)->get();
        return array_reduce($settings->toArray(), function (&$result, $item) {
            $result[$item['key']] = $item['value'];
            return $result;
        });
    }

    public function update($request, $userPid)
    {
        app('db')->beginTransaction();
        $keys = [];
        foreach ($request as $key => $value) {
            StoreSetting::where('user_pid', $userPid)->where('key', $key)->update(['value' => $value]);
            $keys[] = $key;
        }
        $settings = StoreSetting::where('user_pid', $userPid)->whereIn('key', $keys)->get();
        app('db')->commit();
        return array_reduce($settings->toArray(), function (&$result, $item) {
            $result[$item['key']] = $item['value'];
            return $result;
        });
    }
}
