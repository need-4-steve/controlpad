<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use App\Models\StoreSetting;
use App\Models\StoreSettingsKey;

class AddStoreLogoKeyAndSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $storeSettings = StoreSetting::all();
        if (isset($storeSettings) && count($storeSettings) > 0) {
            $showStoreBannerKey = StoreSettingsKey::where('key', 'show_store_banner')->first();
            // If migrations are run in order of made and not in order they were merged, there will be a conflict of keys.
            // This will move the store banner to key_id of 28 if it is 27 and all the settings associated with it.
            if (isset($showStoreBannerKey) && $showStoreBannerKey->id === 27) {
                Schema::disableForeignKeyConstraints();
                DB::beginTransaction();
                $showStoreBannerKey->id = 28;
                $showStoreBannerKey->save();
                $showStoreBanner = StoreSetting::where('key_id', 27)->get();
                foreach ($showStoreBanner as $banner) {
                    $banner->key_id = 28;
                    $banner->save();
                }
                DB::commit();
                Schema::enableForeignKeyConstraints();
            }
            DB::table('store_settings_keys')->insert([
                'id' => 27,
                'key' => 'logo',
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s'),
            ]);
            $reps = User::where('role_id', 5)->get();
            $settings = [];
            foreach ($reps as $rep) {
                $settings[] = [
                    'user_id' => $rep->id,
                    'key_id'  => 27,
                    'value'   => null,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                ];
                cache()->forget('store-settings-'.$rep->id);
            }
            StoreSetting::insert($settings);
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
