<?php

use Illuminate\Database\Seeder;
use App\Models\Order;

class FixPendingCancelations extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $orders = Order::get();
        DB::beginTransaction();
        foreach ($orders as $order) {
            if ($order->status == 'Pending Cancellation') {
                $order->status = 'cancelled';
            }
            $order->save();
        }
        DB::commit();
    }
}
