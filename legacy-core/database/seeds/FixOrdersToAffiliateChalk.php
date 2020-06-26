<?php

use Illuminate\Database\Seeder;
use App\Models\Order;

class FixOrdersToAffiliateChalk extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $orders = Order::where('receipt_id', 'OMIBIK-5765')
            ->orWhere('receipt_id', 'OHKL6E-5771')
            ->orWhere('receipt_id', 'OMS6TB-6164')
            ->orWhere('receipt_id', 'OHO90X-6648')
            ->orWhere('receipt_id', 'OIFOXG-6623')
            ->orWhere('receipt_id', 'ORYRES-6649')
            ->get();
        DB::beginTransaction();
        foreach ($orders as $order) {
            $order->type_id = 9;
            $order->save();
        }
        DB::commit();
    }
}
