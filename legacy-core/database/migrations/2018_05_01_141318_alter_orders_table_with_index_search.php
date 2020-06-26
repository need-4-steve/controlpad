<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOrdersTableWithIndexSearch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('buyer_first_name');
            $table->string('buyer_last_name');
        });

        DB::beginTransaction();

        $orders = \App\Models\Order::all();
        if (!is_null($orders) && count($orders) > 0) {
            foreach ($orders as $order) {
                $user = \App\Models\User::find($order->customer_id);
                if ($user) {
                    $order->buyer_first_name = $user->first_name;
                    $order->buyer_last_name = $user->last_name;
                    $order->save();
                }
            }
        }

        DB::commit();
        // migrate customer names

        DB::statement("ALTER TABLE orders
                        ADD FULLTEXT orders_search_index (receipt_id, buyer_first_name, buyer_last_name)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('buyer_first_name');
            $table->dropColumn('buyer_last_name');
        });
    }
}
