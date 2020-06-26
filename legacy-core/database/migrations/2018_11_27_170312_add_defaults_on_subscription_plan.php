<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultsOnSubscriptionPlan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('slug')->nullable()->change();
            $table->integer('duration')->default(1)->change();
            $table->integer('free_trial_time')->default(0)->change();
            $table->text('description')->nullable()->change();
            $table->boolean('on_sign_up')->default(false)->change();
            $table->string('tax_class')->default('')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->integer('duration')->default(null)->change();
            $table->integer('free_trial_time')->default(null)->change();
            $table->text('description')->nullable(false)->change();
            $table->text('on_sign_up')->default(null)->change();
            $table->string('tax_class')->default(null)->change();
        });
    }
}
