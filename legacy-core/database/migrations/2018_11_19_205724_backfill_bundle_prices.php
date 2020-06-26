<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BackfillBundlePrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('bundles')
        ->join('prices', function ($join) {
            $join->on('prices.priceable_id', '=', 'bundles.id')
                ->where('prices.priceable_type', 'App\Models\Bundle')
                ->where('prices.price_type_id', 1);
        })
        ->update([
            'bundles.wholesale_price' => DB::raw('prices.price'),
        ]);
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
