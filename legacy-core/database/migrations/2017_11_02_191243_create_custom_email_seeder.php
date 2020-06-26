<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;
use App\Models\CustomEmail;

class CreateCustomEmailSeeder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $settings = Setting::where('key', 'company_name')->first();
        $customEmail = CustomEmail::all();
        if (isset($customEmail) && isset($settings) && count($settings) > 0) {
            $seeder = new \CustomEmailSeeder;
            $seeder->run();
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
