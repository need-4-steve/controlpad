<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\StoreSettingsKey;
use App\Models\User;
use App\Models\StoreSetting;
use Carbon\Carbon;

class SettingsBannerImageShow extends Migration
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
            StoreSettingsKey::insert(['id' => 29, 'key' => 'show_banner_image_1', 'created_at' => $now, 'updated_at' => $now,]);
            StoreSettingsKey::insert(['id' => 30, 'key' => 'show_banner_image_2', 'created_at' => $now, 'updated_at' => $now,]);
            StoreSettingsKey::insert(['id' => 31, 'key' => 'show_banner_image_3', 'created_at' => $now, 'updated_at' => $now,]);
            $users = User::where('role_id', 5)->orWhere('id', 1)->get();
            DB::beginTransaction();
            foreach ($users as $user) {
                StoreSetting::insert([
                    ['key_id' => 29, 'key' => 'show_banner_image_1', 'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => true],
                    ['key_id' => 30, 'key' => 'show_banner_image_2', 'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => true],
                    ['key_id' => 31, 'key' => 'show_banner_image_3', 'user_id' => $user->id, 'user_pid' => $user->pid, 'created_at' => $now, 'updated_at' => $now, 'value' => true],
                ]);
            }
            DB::commit();
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
        //
    }
}
