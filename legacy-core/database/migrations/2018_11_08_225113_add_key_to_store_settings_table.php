<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKeyToStoreSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_settings_keys', function (Blueprint $table) {
            $table->string('key')->unique()->change();
        });
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('key');
        });
        DB::table('store_settings')
            ->join('store_settings_keys', 'store_settings_keys.id', '=', 'store_settings.key_id')
            ->update([
                'store_settings.key' => DB::raw('store_settings_keys.key')
            ]);
        Schema::table('store_settings', function (Blueprint $table) {
            $table->foreign('key')->references('key')->on('store_settings_keys');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropForeign('store_settings_key_foreign');
            $table->dropColumn('key');
        });
        Schema::table('store_settings_keys', function (Blueprint $table) {
            $table->dropUnique('store_settings_keys_key_unique');
        });
    }
}
