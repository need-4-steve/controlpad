<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_status', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 30)->unique();
            $table->integer('position');
            $table->boolean('default');
            $table->boolean('visible');
            $table->boolean('login');
            $table->boolean('buy');
            $table->boolean('sell');
            $table->boolean('renew_subscription');
            $table->boolean('rep_locator');
            $table->timestamps();
        });

        DB::table('user_status')->insert([
            'name'                  => 'active',
            'position'              => 1,
            'default'               => true,
            'visible'               => true,
            'login'                 => true,
            'buy'                   => true,
            'sell'                  => true,
            'renew_subscription'    => true,
            'rep_locator'           => true,
            'created_at'            => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s')
        ]);
        DB::table('user_status')->insert([
            'name'                  => 'unregistered',
            'position'              => 2,
            'default'               => true,
            'visible'               => false,
            'login'                 => false,
            'buy'                   => false,
            'sell'                  => false,
            'renew_subscription'    => false,
            'rep_locator'           => false,
            'created_at'            => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s')
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('status')->references('name')->on('user_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['status']);
        });
        Schema::drop('user_status');
    }
}
