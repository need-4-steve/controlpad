<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Inventory;
use App\Models\Invoice;
use Carbon\Carbon;
use App\Repositories\Eloquent\FulfilledByCorporateRepository;

class FixToInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check to make sure invoices are in the system
        if (Invoice::withTrashed()->count() > 0) {
            DB::beginTransaction();
            // update inventory to reset the quantity staged back to 0
            $inventory = Inventory::where('quantity_staged', '!=', 0)->update(['quantity_staged' => 0]);
            // delete invoices that have been expired
            $deletedInvoices = Invoice::where('expires_at', '<=', Carbon::now())->delete();
            // get the invoices that are left over
            $invoices = Invoice::with('items')->get();
            foreach ($invoices as $invoice) {
                foreach ($invoice->items as $item) {
                    // add back valid invoice items to the quantity staged on inventory
                    $inventory = Inventory::where('item_id', $item->item_id)->where('user_id', $invoice->store_owner_user_id)->first();
                    if (isset($inventory)) {
                        $inventory->quantity_staged += $item->quantity;
                        $inventory->save();
                    }
                }
            }
            DB::commit();
        
            // fixes FBC because inventory with quantity staged was preventing FBC products to change ownership back to corporate
            $fbcRepo = new FulfilledByCorporateRepository;
            $fbcRepo->changeOwnerOfSoldOut();
            $fbcRepo->fixInventoryOwnerIdOfZero();
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
