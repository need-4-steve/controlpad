<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Order;

class FixTotalDiscountOnOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $orders = Order::all();
        if (count($orders) > 0) {
            DB::beginTransaction();
            foreach ($orders as $order) {
                $totalCost = 0;
                foreach ($order->lines as $orderline) {
                    $totalCost += $orderline->quantity * $orderline->price;
                }
                $order->total_discount = $totalCost - $order->subtotal_price;
                $order->save();
            }
            DB::commit();
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
