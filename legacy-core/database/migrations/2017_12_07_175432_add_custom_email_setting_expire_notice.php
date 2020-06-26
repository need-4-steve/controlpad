<?php

use App\Models\CustomEmail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomEmailSettingExpireNotice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $check = CustomEmail::where('title', 'expire_notice');
        $customEmail = CustomEmail::all();
        if (isset($customEmail) && count($customEmail) > 0 && ! $check) {
            CustomEmail::create([
                'title' => 'expire_notice',
                'greeting' => 'Dear',
                'content_1' => 'Your subscription will be renewed on',
                'content_2' => '',
                'signature' => ''
            ]);
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
