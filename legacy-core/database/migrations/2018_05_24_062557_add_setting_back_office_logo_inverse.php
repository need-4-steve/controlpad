<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class AddSettingBackOfficeLogoInverse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      $settings = Setting::all();
      if (isset($settings) && count($settings) > 0) {
          Setting::create([
              'user_id' => 1,
              'key' => 'back_office_logo_inverse',
              'value' => '{"value": "https://s3-us-west-2.amazonaws.com/controlpad/Cp-logo-white.png", "show": false}',
              'category' => 'brand',
          ]);
          cache()->forget('globalSettings');
          cache()->forget('global-settings');
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
