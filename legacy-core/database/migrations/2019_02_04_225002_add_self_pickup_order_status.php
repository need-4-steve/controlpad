<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\OrderStatus;

class AddSelfPickupOrderStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $status = OrderStatus::orderBy('position', 'desc')->first();
        if (isset($status)) {
            OrderStatus::create([
                'name'          => 'self-pickup',
                'default'       => true,
                'position'      => $status->position + 1,
                'visible'       => false
            ]);
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
