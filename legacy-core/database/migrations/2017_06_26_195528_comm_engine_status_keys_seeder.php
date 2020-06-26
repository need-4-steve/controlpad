<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\User;

class CommEngineStatusKeysSeeder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $users = User::all();
        // if users are in the system then seed the status keys
        if ((count($users) > 0)) {
            $seeder = new \CommEngineStatusKeysTableSeeder;
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
