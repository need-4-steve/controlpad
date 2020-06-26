<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveSubscriptionPlanPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->double('plan_price', 8, 2)->index();
        });
        DB::table('subscriptions')
            ->join('prices', function ($join) {
                $join->on('prices.priceable_id', '=', 'subscriptions.id')
                    ->where('prices.priceable_type', 'App\Models\Subscription')
                    ->where('prices.price_type_id', 1);
            })
            ->update([
                'subscriptions.plan_price' => DB::raw('prices.price'),
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('plan_price');
        });
    }
}
