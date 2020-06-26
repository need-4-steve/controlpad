<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveSubscriptionUserPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_user', function (Blueprint $table) {
            $table->double('subscription_price', 8, 2)->index();
        });
        DB::table('subscription_user')
            ->join('prices', function ($join) {
                $join->on('prices.priceable_id', '=', 'subscription_user.subscription_id')
                    ->where('prices.priceable_type', 'App\Models\Subscription')
                    ->where('prices.price_type_id', 1);
            })
            ->update([
                'subscription_user.subscription_price' => DB::raw('prices.price'),
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_user', function (Blueprint $table) {
            $table->dropColumn('subscription_price');
        });
    }
}
