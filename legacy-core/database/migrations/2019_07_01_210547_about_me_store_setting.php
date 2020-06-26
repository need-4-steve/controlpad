<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use App\Models\Setting;
use App\Models\StoreSetting;
use App\Models\StoreSettingsKey;
use Carbon\Carbon;

class AboutMeStoreSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $key = StoreSettingsKey::first();

        if (!is_null($key)) {
            $now = Carbon::now('UTC');
            StoreSettingsKey::insert(['id' => 32, 'key' => 'about_me', 'created_at' => $now, 'updated_at' => $now,]);
            $users = User::select('id', 'pid')->where('role_id', 5)->orWhere('id', 1)->get();
            DB::beginTransaction();
            foreach ($users as $user) {
                StoreSetting::insert(
                    [
                        'key_id' => 32,
                        'key' => 'about_me',
                        'user_id' => $user->id,
                        'user_pid' => $user->pid,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'value' => '{"image_url":"","title":"","body":"","facebook_url":"","instagram_url":""}'
                    ]
                );
            }
            DB::commit();
            $settings = Setting::first();
            if (!is_null($settings)) {
                Setting::create([
                    'user_id' => 1,
                    'key' => 'about_rep',
                    'value' => '{"show":false, "value":null}',
                    'category' => 'store'
                ]);
            }
            cache()->flush(); // because of how store settings are cached, it just needs to be flushed
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        StoreSetting::where('key_id', '=', 32)->delete();
        StoreSettingsKey::where('id', '=', 32)->delete();
        Setting::where('key', '=', 'about_rep')->delete();
    }
}
