<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class AddCompanyPidSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $settings = Setting::first();
        if (!is_null($settings)) {
            $companyUser = DB::table('users')->select('pid')->where('id', '=', config('site.apex_user_id'))->first();
            if ($companyUser != null) {
                Setting::create([
                    'user_id' => 1,
                    'key' => 'company_pid',
                    'value' => '{"value": "' . $companyUser->pid . '", "show": true}',
                    'category' => 'general'
                ]);
                cache()->forget('globalSettings');
                cache()->forget('global-settings');
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->where('key', '=', 'company_pid')->delete();
    }
}
