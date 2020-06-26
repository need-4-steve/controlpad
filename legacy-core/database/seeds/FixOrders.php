<?php

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Orderline;

class FixOrders extends Seeder
{
    /**
     * Fixes orders for Shopmaxwell
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        // orders that total_price were 0 that need to be fixed
        $orders = Order::where('transaction_id', '')
            ->with('coupons', 'lines')
            ->get();
        foreach ($orders as $order) {
            $order->total_price = $order->subtotal_price;
            $order->total_discount = 0;
            if (count($order->coupons) > 0) {
                $subtotal = 0;
                foreach ($order->lines as $line) {
                    $subtotal += $line->price * $line->quantity;
                }
                $coupon = $order->coupons[0];

                if ($coupon->is_percent === true) {
                    $discount = $subtotal * ($coupon->amount / 100);
                } else {
                    $discount = $coupon->amount;
                }
                $discount = round($discount, 2);

                if ($discount > $subtotal) {
                    $discount = $subtotal;
                }

                $order->subtotal_price = $subtotal - $discount;
                $order->total_price = $order->subtotal_price + $order->total_shipping + $order->tax_total;
                $order->total_discount = $discount;
            }
            $order->save();
        }

        // orders that were refunded
        $badOrders = Order::whereIn('id', [26, 40, 41, 42])->with('lines')->get();
        foreach ($badOrders as $badOrder) {
            foreach ($badOrder->lines as $line) {
                $line->delete();
            }
            $badOrder->delete();
        }

        // order that had one bad fbc product and one good one
        $badOrderlines = Orderline::whereIn('id', [72, 73, 74, 75])->get();
        foreach ($badOrderlines as $badLine) {
            $badLine->delete();
        }
        $order = Order::where('id', 29)->first();
        $price = $order->lines()->whereNull('item_id')->first()->price;
        $order->subtotal_price = $price;
        $order->total_price = $price;
        $order->save();
        DB::commit();
    }
}
