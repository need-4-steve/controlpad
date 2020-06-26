<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReverseItemPantStripeXsDelete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('COMPANY_NAME') === 'maverickthecollection.com') {
            DB::table('items')
                ->where('id', 1049)
                ->where('size', 'XS')
                ->where('print', 'STRIPE')
                ->update([
                    'manufacturer_sku' => 'PANT-STRIPE-XS',
                    'deleted_at' => null
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
