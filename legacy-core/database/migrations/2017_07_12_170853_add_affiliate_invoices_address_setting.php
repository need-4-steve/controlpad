<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAffiliateInvoicesAddressSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_setting', function (Blueprint $table) {
            $table->boolean('show_address_on_invoice')->default('1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('user_setting', 'show_address_on_invoice')) {
            Schema::table('user_setting', function (Blueprint $table) {
                $table->dropColumn('show_address_on_invoice');
            });
        }
    }
}
