<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Order;

class FixTotalDiscountOnOrders2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get Orders that had both a Bundle and not a bundle in it. Discounts were wrong on these ones.
        $orders = Order::whereHas('lines', function ($query) {
            $query->where('type', 'Bundle');
        })
        ->whereHas('lines', function ($query) {
            $query->where('type', '!=', 'Bundle');
        })
        // or Get orders that have a negative discount to make sure we get them all.
        ->orWhere('total_discount', '<', 0)
        ->get();
        DB::beginTransaction();
        foreach ($orders as $order) {
            $totalCost = 0;
            // Add up orderlines to see what the cost actually was.
            foreach ($order->lines as $orderline) {
                $totalCost += $orderline->quantity * $orderline->price;
            }
            // See what the actuall discount amount was suppose to be. $order->subtotal_price is the correct price.
            $totalDiscount = $totalCost - $order->subtotal_price;
            // Compare previous discount with new discount. Add a database transaction to save if it is different.
            if (round($totalDiscount, 2) !== $order->total_discount) {
                $order->total_discount = $totalDiscount;
                $order->save();
            }
        }
        DB::commit();
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
