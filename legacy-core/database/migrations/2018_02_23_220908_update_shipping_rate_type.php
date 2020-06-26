<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\ShippingRate;

class UpdateShippingRateType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $shippingRates = ShippingRate::all();
        if (isset($shippingRates) && count($shippingRates) > 0) {
            DB::beginTransaction();
            foreach ($shippingRates as $shippingRate) {
                $shippingRate->update(['type' =>'retail']);
                if ($shippingRate->user_id === 1) {
                    ShippingRate::create([
                        'user_id' => $shippingRate->user_id,
                        'amount' => $shippingRate->amount,
                        'min' => $shippingRate->min,
                        'max' => $shippingRate->max,
                        'type' => 'wholesale',
                        'name' => $shippingRate->name
                    ]);
                }
            }
            DB::commit();
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
