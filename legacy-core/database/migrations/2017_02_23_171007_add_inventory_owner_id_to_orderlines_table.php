<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Orderline;

class AddInventoryOwnerIdToOrderlinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orderlines', function (Blueprint $table) {
            $table->integer('inventory_owner_id')->nullable();
        });

        $orderlines = Orderline::with('order')->get();
        DB::beginTransaction();
        if ($orderlines) {
            foreach ($orderlines as $orderline) {
                $orderline->inventory_owner_id = $orderline->order->store_owner_user_id;
                $orderline->save();
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
        Schema::table('orderlines', function (Blueprint $table) {
            $table->dropColumn('inventory_owner_id');
        });
    }
}
