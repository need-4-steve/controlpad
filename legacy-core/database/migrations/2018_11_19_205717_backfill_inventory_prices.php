<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BackfillInventoryPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('inventories')
        ->join('prices', function ($join) {
            $join->on('prices.priceable_id', '=', 'inventories.id')
                ->where('prices.priceable_type', 'App\Models\Inventory')
                ->where('prices.price_type_id', 4);
        })
        ->update([
            'inventories.inventory_price' => DB::raw('prices.price'),
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
