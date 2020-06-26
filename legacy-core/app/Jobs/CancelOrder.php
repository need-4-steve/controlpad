<?php

namespace App\Jobs;

use App\Jobs\CancelCommEngineOrder;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Orderline;
use App\Models\Price;
use App\Models\ReturnTransaction;
use App\Models\Returnline;
use App\Models\ReturnModel;
use App\Models\ReturnHistory;
use App\Services\PayMan\PayManService;
use App\Services\Tax\TaxService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CancelOrder implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    protected $paymentManager;
    protected $input;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order, $input = null)
    {
        $this->order = $order;
        $this->input = $input;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->order->status == 'fulfilled') {
            $returnAmount = ReturnTransaction::where('return_id', $this->input['id'])->first();
            $this->order->total_price = $returnAmount->amount;
        } else {
            $this->order->status = 'cancelled';
            $this->order->save();
        }

        // have to do it this way or it gives job serialization errors:
        $paymentManager = new PayManService;
        try {
            // refund payment
            $orderlines = Orderline::where('order_id', $this->order->id)->get();
            $fbcUser = [];
            $lineqty = 0;
            foreach ($orderlines as $line) {
                $lineqty += $line->quantity;
                if ($line->inventory_owner_id !== $this->order->store_owner_user_id && isset($returnAmount) && $returnAmount->amount > 0) {
                    $amount = Returnline::where('return_id', $returnAmount->return_id)->where('orderline_id', $line->id)->first();
                }
                if (isset($amount)
                    && $amount->return_amount !== null
                    && $amount->return_amount > 0
                    && $line->inventory_owner_id !== config('site.apex_user_id')) {
                    $fbcUser[] = ['payeeUserId' => $line->inventory_owner_id, 'amount' => $amount->return_amount];
                } elseif ($line->inventory_owner_id !== $this->order->store_owner_user_id
                    && !isset($returnAmount)
                    && $line->inventory_owner_id !== config('site.apex_user_id')) {
                    $fbcUser[] = ['payeeUserId' => $line->inventory_owner_id, 'amount' => (($line->price * $line->quantity) - $line->discount_amount)];
                }
            }
            if ($this->input === null || $this->input !== null && array_sum(array_column($this->input['lines'], 'quantity')) === $lineqty) {
                $refund = $paymentManager->refundOrder($this->order, $refundType = 'refund', $fbcUser);
            } else {
                // here is where we need to account for taxes return on partial refund.
                $this->order->total_tax = 0;
                $refund = $paymentManager->refundOrder($this->order, $refundType = 'refund', $fbcUser);
            }

            if (isset($returnAmount) && isset($refund['success']) && $refund['success'] == true) {
                $returnAmount->update([
                    'transaction_id' => $refund['transaction']['id'],
                    'amount' => $refund['transaction']['amount'],
                    'type' => $refund['transaction']['transactionType']
                ]);
            }
            if (isset($returnAmount) && isset($refund['error'])) {
                // log error
                logger()->error($refund['error']);
                // set return back to pending with comment about error.
                $returnStatus = ReturnModel::where('id', $this->input['id'])->first();
                $returnStatus->update(['return_status_id' => 2]);
                $returnHistory = ReturnHistory::create([
                    'return_id'     => $this->input['id'],
                    'user_id'       => $this->input['user_id'],
                    'old_status_id' => 3,
                    'new_status_id' => 2,
                    'comments'      => $refund['error'],
                ]);
                return;
            }
            // refund tax invoice
            if ($this->order->tax_invoice_pid != null) {
                $taxResponse = (new TaxService)->refundId($this->order->tax_invoice_pid);
                if (isset($taxResponse->error)) {
                    logger()->error('Failed to return taxInvoice for order: ' . $this->order->id);
                }
            }
            // cancel order in commission engine
            $job = new CancelCommEngineOrder($this->order, false);
            dispatch($job);

            // return inventory to user
            $order = $this->order->load('lines');
            foreach ($order->lines as $line) {
                $quantity = $line->quantity;
                $owner = $line->inventory_owner_id;
                $inventory = Inventory::where('user_id', $owner)
                                ->where('item_id', $line->item_id)
                                ->first();
                if (!empty($inventory)) {
                    $inventory->quantity_available += $quantity;
                    $inventory->save();
                } elseif (empty($inventory) && $line->item_id !== null) {
                    logger()->error('canceled order inventory not found', [
                        'fingerprint' => 'canceled order inventory not found',
                        'orderline' => $line
                    ]);
                }
              if ($order->type_id === 1 && $order->source === 'Web' && !is_null($order->inventory_received_at)) {
                  $repInventory = Inventory::where('user_id', $order->customer_id)
                                    ->where('item_id', $line->item_id)->first();
                  if (!empty($repInventory)) {
                      $repInventory->quantity_available -= $line->quantity;
                      $repInventory->save();
                  } elseif (empty($repInventory) && $line->item_id !== null) {
                    logger()->error('canceled order inventory for rep not found', [
                        'fingerprint' => 'canceled order inventory for rep not found',
                        'orderline' => $line
                    ]);
                  }

              }
            }
        } catch (Exception $e) {
            logger()->error(['error' => $e->getMessage()]);
        }
    }
}
