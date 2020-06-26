<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class ChangeDefaultLogingOptions extends Migration
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
          DB::beginTransaction();
              $facebookLogin = Setting::where('key', 'rep_facebook_login')->first();
              $facebookLogin->update(['value' => '{"value": "facebook logins", "show": false}']);
              $instagramLogin = Setting::where('key', 'rep_instagram_login')->first();
              $instagramLogin->update(['value' => '{"value": "instagram logins", "show": false}']);
              $gmailLogin = Setting::where('key', 'rep_gmail_login')->first();
              $gmailLogin->update(['value' => '{"value": "gmail logins", "show": false}']);
          DB::commit();
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
