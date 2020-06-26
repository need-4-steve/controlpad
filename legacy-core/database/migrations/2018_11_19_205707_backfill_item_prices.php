<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BackfillItemPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('items')
        ->join('prices as wholesale', function ($join) {
            $join->on('wholesale.priceable_id', '=', 'items.id')
                ->where('wholesale.priceable_type', 'App\Models\Item')
                ->where('wholesale.price_type_id', 1);
        })
        ->join('prices as retail', function ($join) {
            $join->on('retail.priceable_id', '=', 'items.id')
                ->where('retail.priceable_type', 'App\Models\Item')
                ->where('retail.price_type_id', 2);
        })
        ->join('prices as premium', function ($join) {
            $join->on('premium.priceable_id', '=', 'items.id')
                ->where('premium.priceable_type', 'App\Models\Item')
                ->where('premium.price_type_id', 3);
        })
        ->update([
            'items.retail_price' => DB::raw('retail.price'),
            'items.wholesale_price' => DB::raw('wholesale.price'),
            'items.premium_price' => DB::raw('premium.price'),
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
