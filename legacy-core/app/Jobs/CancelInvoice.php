<?php

namespace App\Jobs;

use DB;
use App\Models\Invoice;
use App\Models\ReturnTransaction;
use App\Models\Inventory;
use App\Repositories\Eloquent\InventoryRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CancelInvoice implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $invoice;
    protected $invoice_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($invoice_id)
    {
        $this->invoice_id = $invoice_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Backwards compatible
            if (isset($this->invoice)) {
                $this->invoice_id = $this->invoice->id;
            }

            // return items to store owner's inventory
            $this->invoice = Invoice::where('id', '=', $this->invoice_id)->with('lines')->first();
            if ($this->invoice === null) {
                logger()->error(
                    'Invoice missing for cancel',
                    [
                        'id' => $this->invoice_id
                    ]
                );
                return;
            }
            foreach ($this->invoice->lines as $line) {
                $quantity = $line->pivot->quantity;
                if ($this->invoice->type_id === 9) {
                    $inventoryOwnerId = config('site.apex_user_id');
                } else {
                    $inventoryOwnerId = $this->invoice->store_owner_user_id;
                }
                $inventory = Inventory::where('user_id', $inventoryOwnerId)
                                ->where('item_id', $line->id)
                                ->first();
                if (!empty($inventory)) {
                    // Increment at the database level to prevent race conditions
                    DB::statement(
                        'UPDATE inventories SET quantity_available = quantity_available + ?, quantity_staged = quantity_staged - ? WHERE id = ?',
                        [$quantity, $quantity, $inventory->id]
                    );
                    DB::table('inventory_history')->insert([
                        'inventory_id'                  => $inventory->id,
                        'inventory_user_id'             => $inventory->user_id,
                        'item_id'                       => $inventory->item_id,
                        'before_quantity_available'     => $inventory->quantity_available,
                        'after_quantity_available'      => $inventory->quantity_available + $quantity,
                        'before_quantity_staged'        => $inventory->quantity_staged,
                        'after_quantity_staged'         => $inventory->quantity_staged - $quantity,
                        'request_path'                  => '/invoices',
                        'application'                   => 'Core'
                    ]);
                } else {
                    // For now just log inventory missing because it would point to a greater problem
                    // Previously threw an exception that prevented rest of lines from reverting and invoice from deleting
                    // Current code for creating inventory records isn't race condition safe anyway
                    logger()->error(
                        'No inventory for invoice cancel',
                        [
                            'invoice_id' => $this->invoice_id,
                            'item_id' => $line->id
                        ]
                    );
                }
            }
            $this->invoice->delete();
        } catch (\Exception $e) {
            logger()->error(['error' => $e->getMessage()]);
        }
    }
}
