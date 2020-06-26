<?php

use App\Models\Address;
use App\Models\Order;
use App\Models\Orderline;
use App\Models\User;
use App\Models\Item;
use Carbon\Carbon;

class OrdersTableSeeder extends DatabaseSeeder
{

    public function run()
    {
        DB::beginTransaction();

        $apexUser = User::where('id', config('site.apex_user_id'))->first();
        $reps = User::where('role_id', 5)->get();

        foreach ($reps as $rep) {
            // orders that are from corporate to a rep
            factory(Order::class, 6)->create([
                'customer_id' => $rep->id,
                'source' => null
            ])->each(function ($order) use ($apexUser, $rep) {
                $lines = factory(Orderline::class, 'wholesale', rand(2, 5))->create([
                    'order_id' => $order->id,
                    'inventory_owner_id' => $apexUser->id
                ]);
                if (!$apexUser->customers()->where('customer_id', $rep->id)->first()) {
                    $apexUser->customers()->attach($rep->id);
                }
            });

            // orders that are from a rep to a customer
            factory(Order::class, 6)->create([
                'store_owner_user_id' => $rep->id,
                'type_id' => 3,
                'cash' => random_int(0, 1)
            ])->each(function ($order) use ($rep) {
                $lines = factory(Orderline::class, 'retail', rand(2, 5))->create([
                    'order_id' => $order->id,
                    'inventory_owner_id' => $rep->id
                ]);
                $customer = factory(User::class, 'customer', 1)->create();

                // creates addresses for customer
                $billingAddress = factory(Address::class, 1)->make([
                    'name' => $customer->first_name.' '.$customer->last_name,
                    'label' => 'Billing',
                ])->toArray();
                unset($billingAddress['id']);
                $billingAddress['addressable_id'] = $customer->id;
                $billingAddress['addressable_type'] = User::class;
                $shippingAddress = $billingAddress;
                $shippingAddress['label'] = 'Shipping';

                DB::table('addresses')->insert($billingAddress);
                DB::table('addresses')->insert($shippingAddress);

                $order->update([
                    'customer_id' => $customer->id
                ]);
                $rep->customers()->attach($customer);
            });
        }

        // calculates correct totals for all orders
        Order::all()->each(function ($order) {
            // adds up price of orderlines
            $subtotal = 0;
            foreach ($order->lines as $line) {
                $subtotal += $line->quantity * $line->price;
            }

            // calculates price of shipping depending on subtotal price
            switch (true) {
                case ($subtotal <= 30):
                    $shipping = 7.00;
                    break;
                case ($subtotal  <= 55):
                    $shipping = 13.00;
                    break;
                case ($subtotal < 80):
                    $shipping = 19.00;
                    break;
                default:
                    $shipping = 0;
            }

            $tax = round($subtotal * .075, 2);
            $total = $subtotal + $tax + $shipping;

            $order->update([
                'receipt_id' => "S".strtoupper(str_random(5)).'-'.$order->id,
                'subtotal_price' => $subtotal,
                'total_tax' => $tax,
                'total_shipping' => $shipping,
                'total_price' => $total,
            ]);

            // creates billing and shipping addresses for the order
            $customer = User::where('id', $order->customer_id)->first();
            $billingAddress = $customer->billingAddress()->first()->toArray();
            unset($billingAddress['id']);
            $billingAddress['addressable_id'] = $order->id;
            $billingAddress['addressable_type'] = Order::class;
            $shippingAddress = $billingAddress;
            $shippingAddress['label'] = 'Shipping';

            DB::table('addresses')->insert($billingAddress);
            DB::table('addresses')->insert($shippingAddress);

            if ($order->created_at < Carbon::now()->subdays(9)) {
                $order->status = 'fulfilled';
                $order->save();
            }
        });
        DB::commit();
    }
}
