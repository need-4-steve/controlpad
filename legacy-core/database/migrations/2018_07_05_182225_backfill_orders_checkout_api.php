<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use CPCommon\Pid\Pid;

class BackfillOrdersCheckoutApi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $lastId = 0;
        // Create a temporary table to batch pids into that will be joined after
        // This prevents iteration of updates
        Schema::dropIfExists('order_pid_fill'); // make sure table doesn't exist
        Schema::create('order_pid_fill', function (Blueprint $table) {
            $table->integer('id')->unique();
            $table->string('pid', 25);
            $table->text('billing_address')->nullable(true);
            $table->text('shipping_address')->nullable(true);
        });
        while (true) {
            $orders = DB::table('orders as o')->select(
                'o.id',
                'ba.address_1 as ba_line_1',
                'ba.address_2 as ba_line_2',
                'ba.city as ba_city',
                'ba.state as ba_state',
                'ba.zip as ba_zip',
                'sa.address_1 as sa_line_1',
                'sa.address_2 as sa_line_2',
                'sa.city as sa_city',
                'sa.state as sa_state',
                'sa.zip as sa_zip'
            )
            ->leftJoin('addresses as ba', function ($q) {
                $q->on('ba.addressable_id', '=', 'o.id')
                ->where('ba.addressable_type', '=', App\Models\Order::class)
                ->where('ba.label', '=', 'Billing');
            })
            ->leftJoin('addresses as sa', function ($q) {
                $q->on('sa.addressable_id', '=', 'o.id')
                ->where('sa.addressable_type', '=', App\Models\Order::class)
                ->where('sa.label', '=', 'Shipping');
            })
            ->whereNull('o.pid')
            ->where('o.id', '>', $lastId)->limit(1000)->get();
            if (empty($orders) || count($orders) == 0) {
                break;
            }
            $lastId = $orders->last()->id;
            $data = [];
            foreach ($orders as $key => $order) {
                $data[] = [
                    'id' => $order->id,
                    'pid' => Pid::create(),
                    // Serialize order billing address
                    'billing_address' => (isset($order->ba_zip) ? json_encode(
                        [
                            'line_1' => $order->ba_line_1,
                            'line_2' => $order->ba_line_2,
                            'city' => $order->ba_city,
                            'state' => $order->ba_state,
                            'zip' => $order->ba_zip
                        ]
                    ) : null),
                    // Serialize order shipping address
                    'shipping_address' => (isset($order->sa_zip) ? json_encode(
                        [
                            'line_1' => $order->sa_line_1,
                            'line_2' => $order->sa_line_2,
                            'city' => $order->sa_city,
                            'state' => $order->sa_state,
                            'zip' => $order->sa_zip
                        ]
                    ) : null)
                ];
            }
            DB::table('order_pid_fill')->insert($data);
        };
        // Backfill order pid, buyer_email, buyer_pid, seller_pid, seller_name, confirmation_number
        DB::update(
            'update orders as o ' .
            'left join order_pid_fill as opf on opf.id = o.id ' .
            'left join users as buyer on o.customer_id = buyer.id ' .
            'left join users as seller on o.store_owner_user_id = seller.id ' .
            'set o.pid = opf.pid, o.buyer_pid = buyer.pid, o.buyer_email = buyer.email, ' .
            'o.billing_address = opf.billing_address, o.shipping_address = opf.shipping_address, ' .
            'o.seller_pid = seller.pid, o.seller_name = concat_ws(" ", seller.first_name, seller.last_name), ' .
            'o.confirmation_code = o.receipt_id ' .
            'where o.pid is null'
        );
        // Delete temporary table
        Schema::dropIfExists('order_pid_fill');

        // Backfill coupon id
        DB::update(
            "update orders as o " .
            "join applied_coupons as ac on o.id = ac.couponable_id " .
            "set o.coupon_id = ac.coupon_id " .
            "where ac.couponable_type = ?",
            [App\Models\Order::class]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Do nothing
    }
}
