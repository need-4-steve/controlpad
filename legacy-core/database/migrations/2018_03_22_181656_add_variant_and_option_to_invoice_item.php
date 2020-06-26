<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVariantAndOptionToInvoiceItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice_item', function (Blueprint $table) {
            $table->string('variant')->default('');
            $table->string('option')->default('');
        });
        DB::table('invoice_item')
            ->join('items', 'items.id', '=', 'invoice_item.item_id')
            ->join('variants', 'variants.id', '=', 'items.variant_id')
            ->update([
                'option' => DB::raw('items.size'),
                'variant' => DB::raw('variants.name')
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_item', function (Blueprint $table) {
            $table->dropColumn(['variant', 'option']);
        });
    }
}
