<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\OrderStatus;
use App\Models\User;

class CreateOrderStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_status', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 30)->unique();
            $table->integer('position');
            $table->boolean('default');
            $table->boolean('visible');
            $table->timestamps();
        });

        $users = User::first();
        if (isset($users) && count($users) > 0) {
            DB::beginTransaction();
            OrderStatus::create([
                'name'          => 'unfulfilled',
                'position'      => 1,
                'visible'       => true
            ]);
            OrderStatus::create([
                'name'          => 'fulfilled',
                'position'      => 2,
                'visible'       => true
            ]);
            OrderStatus::create([
                'name'          => 'hold',
                'position'      => 3,
                'visible'       => false
            ]);
            OrderStatus::create([
                'name'          => 'cancelled',
                'position'      => 4,
                'visible'       => false
            ]);
            DB::table('order_status')->update(['default' => true]);
            DB::commit();
            $orderStatus = OrderStatus::select('id', 'name', 'position', 'visible', 'default')->orderBy('position')->get();
            cache()->forever('order-status', $orderStatus);
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('status')->references('name')->on('order_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['status']);
        });
        Schema::drop('order_status');
    }
}
