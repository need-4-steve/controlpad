<?php

namespace App\Repositories\Eloquent;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryContract;
use App\Repositories\Eloquent\OrderRepository;
use App\Services\Inventory\InventoryService;
use App\Models\User;
use Carbon\Carbon;
use Cache;
use DB;

class InvoiceRepository implements InvoiceRepositoryContract
{
    public function __construct(OrderRepository $orderRepo, InventoryService $inventoryService)
    {
        $this->orderRepo = $orderRepo;
        $this->inventoryService = $inventoryService;
        $this->settings = app('globalSettings');
    }
    /**
     * Find an instances of InvoiceRepository
     *
     * @param array $inputs
     * @return bool|InvoiceRepository
     */
    public function find($token)
    {
        $invoice = Invoice::with('user.billingAddress', 'user.shippingAddress', 'items', 'invoiceItems.msrp', 'user')
            ->where('token', $token)
            ->where('expires_at', '>', date('Y-m-d H:i:s'));
        $invoice->with(['invoiceItems' => function ($query) {
            $query->select('items.*', 'invoice_item.variant', 'invoice_item.option')->with('product.media', 'variant.media');
        }]);
        return $invoice->first();
    }

    /**
     * Build an index query, return a query builder instance
     *
     * @param array $request
     * @return QueryBuilderInstance
     */
    public function buildInvoiceIndexQuery($request)
    {
        if ($request['column'] == 'status') {
            $request['column'] = 'created_at';
        }
        $invoiceQuery = Invoice::with('orderType')
                ->select(
                    'invoices.*',
                    'users.first_name as customer_first_name',
                    'users.last_name as customer_last_name',
                    'invoices.token as receipt_id',
                    'seller.first_name as seller_first_name',
                    'seller.last_name as seller_last_name'
                )
                ->join('users', 'users.id', '=', 'invoices.customer_id')
                ->join('users as seller', 'seller.id', '=', 'invoices.store_owner_user_id')
                ->where('expires_at', '>', date('Y-m-d H:i:s'));

        if (! empty($request['customer_id'])) {
            $invoiceQuery->where('invoices.customer_id', $request['customer_id']);
        }

        if (! empty($request['type'])) {
            $invoiceQuery->where('invoices.type_id', $request['type']);
        }

        if (! empty($request['store_owner_user_id'])) {
            if (!empty($request['status']) && $request['status'] === 'owner') {
                $invoiceQuery->where('store_owner_user_id', $request['store_owner_user_id']);
            } elseif (!empty($request['status']) && $request['status'] === 'rep') {
                $invoiceQuery->where('store_owner_user_id', '<>', $request['store_owner_user_id']);
            }
        }
        $invoiceQuery->whereBetween('invoices.created_at', [$request['start_date'], $request['end_date']])
                     ->where(function ($query) use ($request) {
                        $query->where('invoices.token', 'LIKE', '%' . $request['search_term'] . '%')
                        ->orWhere('users.first_name', 'LIKE', '%' . $request['search_term'] . '%')
                        ->orWhere('users.last_name', 'LIKE', '%' . $request['search_term'] . '%');
                     })
                    ->orderBy($request['column'], $request['order']);

        return $invoiceQuery;
    }

    /**
     * Create a new instances of InvoiceRepository
     *
     * @param array $inputs
     * @return bool|InvoiceRepository
     */
    public function create(array $inputs = [], array $itemsPivot = [])
    {
        $fields = [
            'customer_id',
            'store_owner_user_id',
            'subtotal_price',
            'total_shipping',
            'total_discount',
            'coupon_code',
            'couponable'
        ];
        $invoice = new Invoice;
        foreach ($fields as $field) {
            $invoice->$field = array_get($inputs, $field);
        }

        $invoice->token = time().str_random(20);
        $hours = $this->settings->getGlobal('einvoice_expire_time', 'value');
        if ($hours < 1) {
            $hours = 1;
        }
        $invoice->expires_at = Carbon::now()->addHours($hours)->toDateTimeString();

        $invoice->type_id = $this->orderRepo->orderType(
            $inputs['store_owner_user_id'],
            $inputs['customer_id']
        );

        $invoice->save();
        $invoice->invoiceItems()->sync($itemsPivot);
        DB::table('invoice_item')
            ->where('invoice_id', $invoice->id)
            ->join('items', 'items.id', '=', 'invoice_item.item_id')
            ->join('variants', 'variants.id', '=', 'items.variant_id')
            ->update([
                'option' => DB::raw('items.size'),
                'variant' => DB::raw('variants.name')
            ]);
        return $invoice->load('items', 'user.addresses', 'user.phone');
    }

    /**
     * Remove invoices that have already expired.
     *
     * @param array $inputs
     * @return bool|InvoiceRepository
     */
    public function removeExpiredInvoices()
    {
        $expiredInvoices = Invoice::with('invoiceItems')
                            ->where('expires_at', '<=', Carbon::now())
                            ->get();
        foreach ($expiredInvoices as $invoice) {
            $store_owner_user_id = $invoice->store_owner_user_id;
            if ($invoice->type_id === 9) {
                $store_owner_user_id = config('site.apex_user_id');
            }
            $inventory = $this->inventoryService->unstageInvoice($invoice->invoiceItems, $store_owner_user_id);
            $invoice->delete();
        }
    }

    /**
     * Generate a unique id for the invoice to show on orderpage.
     *
     * @return string
     */
    public function genUID()
    {
        return "I" . strtoupper(str_random(5));
    }
}
