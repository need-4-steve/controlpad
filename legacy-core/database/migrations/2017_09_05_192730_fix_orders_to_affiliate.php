<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Order;

class FixOrdersToAffiliate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $orders = Order::where('receipt_id', 'OWTA5Q-3726')
            ->orWhere('receipt_id', 'OF2LDC-3629')
            ->orWhere('receipt_id', 'OW9TF0-3730')
            ->orWhere('receipt_id', 'O1HFML-2994')
            ->get();
        DB::beginTransaction();
        foreach ($orders as $order) {
            $order->type_id = 9;
            $order->save();
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
