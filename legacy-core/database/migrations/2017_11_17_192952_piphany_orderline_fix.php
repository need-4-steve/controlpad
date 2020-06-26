<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Order;
use App\Models\Orderlines;

class PiphanyOrderlineFix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (config('site.company_name') == 'Piphany') {
            $orders = Order::with('lines')
                ->where('created_at', '>', '2017-11-16')
                ->where('created_at', '<', '2017-11-18')
                ->get();

            foreach ($orders as $order) {
                $itemIds = [];
                $itemIdsQuantity = [];
                foreach ($order->lines as $line) {
                    // duplicate
                    if (in_array($line->item_id, $itemIds)) {
                        // double check that we are same quantity, only delete if same
                        if ($itemIdsQuantity[$line->item_id] == $line->quantity) {
                            $line->delete();
                        }
                        // different quantity, different bundle
                    } else {
                        // store this one
                        $itemIds[] = $line->item_id;
                        $itemIdsQuantity[$line->item_id] = $line->quantity;
                    }
                }
            }
        }
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
