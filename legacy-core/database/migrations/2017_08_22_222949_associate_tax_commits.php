<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\TaxInvoice;
use App\Models\Order;

class AssociateTaxCommits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $invoices = TaxInvoice::where('taxable_id', '!=', 0)->where('taxable_type', '=', '')->get();
        DB::beginTransaction();
        foreach ($invoices as $invoice) {
            // fixes tax invoices that have the taxable id but not the morphable object
            $invoice->taxable_type = Order::class;
            $invoice->save();
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
        //
    }
}
