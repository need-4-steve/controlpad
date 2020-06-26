<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WoocomZoomUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('zoom_user')) {
            Schema::table('zoom_user', function (Blueprint $table) {
                $table->string('woocom_customer_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('zoom_user')) {
            Schema::table('zoom_user', function (Blueprint $table) {
                $table->dropColumn('woocom_customer_id');
            });
        }
    }
}
