<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\InvoiceRepository;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\UserSettingsRepository;
use App\Repositories\Eloquent\CancellationRepository;
use App\Services\Inventory\InventoryService;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function __construct(
        CancellationRepository $cancellationRepo,
        InventoryService $inventoryService,
        InvoiceRepository $invoiceRepo,
        AuthRepository $authRepo,
        UserSettingsRepository $userSettingsRepo
    ) {
        $this->cancellationRepo = $cancellationRepo;
        $this->inventoryService = $inventoryService;
        $this->invoiceRepo = $invoiceRepo;
        $this->authRepo = $authRepo;
        $this->userSettingsRepo = $userSettingsRepo;
    }

    public function getAllInvoices()
    {
        $request = request()->all();

        $request['store_owner_user_id'] = $this->authRepo->getOwnerId();
        $timezone = $this->userSettingsRepo->getUserTimeZone($request['store_owner_user_id']);

        if (isset($request['start_date']) and $request['start_date'] != 'Invalid date') {
            $request['start_date'] = Carbon::parse($request['start_date'], $timezone)->startOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
        } else {
            $request['start_date'] = Carbon::now()->startOfCentury()->setTimezone('UTC')->format('Y-m-d H:i:s');
        }
        if (isset($request['end_date']) and $request['end_date'] != 'Invalid date') {
            $request['end_date'] = Carbon::parse($request['end_date'], $timezone)->endOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
        } else {
            $request['end_date'] = Carbon::now()->endOfCentury()->setTimezone('UTC')->format('Y-m-d H:i:s');
        }

        $invoices = $this->invoiceRepo->buildInvoiceIndexQuery($request)->get();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        //Define how many items we want to be visible in each page
        $perPage = isset($request['per_page']) ? $request['per_page'] : 5;

        // offset a problem determining starting point when on page 1
        $startingPoint = 0;
        if ($currentPage > 1) {
            $startingPoint = ($currentPage - 1) * $perPage;
        }

        //Slice the collection to get the items to display in current page
        $currentPageSearchResults = $invoices->slice($startingPoint, $perPage)->all();

        //Create our paginator and pass it to the view
        $paginatedSearchResults = new LengthAwarePaginator($currentPageSearchResults, count($invoices), $perPage);

        return ['orders' => $paginatedSearchResults];
    }

    public function getAllInvoicesForPdf()
    {
        $request = request()->all();

        if (isset($request['seller_id']) && $this->authRepo->isAdmin()) {
            // Admin can select any user
            $request['store_owner_user_id'] = $request['seller_id'];
        } else {
            $request['store_owner_user_id'] = $this->authRepo->getOwnerId();
        }

        $invoices = json_decode(json_encode($this->invoiceRepo->buildInvoiceIndexQuery($request)->paginate($request['per_page'])));
        $invoiceIds = [];
        $invoiceMap = [];
        foreach ($invoices->data as $key => $invoice) {
            $invoice->items = [];
            $invoiceIds[] = $invoice->id;
            $invoiceMap[$invoice->id] = $invoice;
        }

        $items = InvoiceItem::select('invoice_item.*', 'items.id', 'products.name', 'items.custom_sku', 'items.manufacturer_sku as sku')
        ->join('items', 'invoice_item.item_id', 'items.id')
        ->join('products', 'items.product_id', 'products.id')
        ->whereIn('invoice_item.invoice_id', $invoiceIds)
        ->get();

        foreach ($items as $key => $item) {
            $invoiceMap[$item->invoice_id]->items[] = $item;
        }
        return response()->json($invoices);
    }

    public function getPdfInvoice($uid)
    {
        $invoice = Invoice::select(
            'invoices.*',
            'users.first_name as customer_first_name',
            'users.last_name as customer_last_name',
            'users.email as customer_email',
            'invoices.token as receipt_id'
        )
        ->join('users', 'users.id', '=', 'invoices.customer_id')->where('uid', $uid)->first();
        if (!$invoice) {
            return response()->json(['message' => 'Could not find a valid invoice.'], 400);
        }
        if ($invoice->store_owner_user_id != $this->authRepo->getOwnerId() && !$this->authRepo->isAdmin()) {
            return response()->json('Must be store owner or admin', 403);
        }

        $invoice = json_decode(json_encode($invoice));

        $items = InvoiceItem::select('invoice_item.*', 'items.id', 'products.name', 'items.custom_sku', 'items.manufacturer_sku as sku')
        ->join('items', 'invoice_item.item_id', 'items.id')
        ->join('products', 'items.product_id', 'products.id')
        ->where('invoice_item.invoice_id', $invoice->id)
        ->get();

        $invoice->items = $items;
        return response()->json($invoice);
    }

    public function show($token)
    {
        $invoice = $this->invoiceRepo->find($token);

        if (!$invoice) {
            return response()->json(['message' => 'Could not find a valid invoice.'], 400);
        }
        $invoice->customer = $invoice->user;
        $invoice->receipt_id = $invoice->token;
        $invoice->shipping_address = $invoice->user->shippingAddress;
        $invoice->billing_address = $invoice->user->billingAddress;
        return response()->json($invoice, 200);
    }

    /**
     * bulk invoice status update
     *
     * @param none
     * @return Json
     */
    public function updateInvoiceStatus()
    {
        $request = request()->all();
        if (! isset($request['status']) ||
            $request['status'] != 'cancelled') {
            return response()->json(['error' => 'No or invalid status submitted to change'], 422);
        }

        $request['status'] = strtolower($request['status']);

        if (! isset($request['invoices'])) {
            return response()->json(['error' => 'No orders submitted'], 422);
        }

        $invoiceIds = [];
        foreach ($request['invoices'] as $invoiceId) {
            if (strlen($invoiceId) < 16) {
                $invoiceIds[] = $invoiceId;
            }
        }

        $invoices = Invoice::whereIn('uid', $invoiceIds)->get();

        // If we have a cancellation request
        if ($request['status'] == 'cancelled') {
            $cancelStatus = $this->cancellationRepo->addInvoicesToCancellationQueue($invoices);
            if (isset($cancelStatus['error'])) {
                return response()->json(['error' => $cancelStatus['error']], 422);
            }
        }

        return response()->json($invoices, 200);
    }

    public function applyCoupon($uid)
    {
        $couponCode = request()->input('coupon_code');
        if (empty($couponCode)) {
            return response()->json(['coupon_code' => ['Coupon code required']], 422);
        }
        $invoice = Invoice::where('uid', '=', $uid)->first();
        if ($invoice === null) {
            return response()->json(['message' => 'Invoice missing'], 404);
        }
        if (!$invoice->couponable) {
            return response()->json(['message' => 'Not allowed to apply coupon to this invoice'], 400);
        }
        if ($invoice->type_id === 9) {
            $ownerId = config('site.apex_user_id');
        } else {
            $ownerId = $invoice->store_owner_user_id;
        }

        $coupon = \App\Models\Coupon::where('code', $couponCode)
                    ->where('owner_id', $ownerId)
                    ->where('type', 'retail')
                    ->first();
        if ($coupon === null) {
            return response()->json(['coupon_code' => ['Coupon missing']], 422);
        }

        if ($coupon->uses >= $coupon->max_uses) {
            return response()->json(['coupon_code' => ['Coupon code has reached it\'s maximum uses.']], 422);
        }
        if ($coupon->expires_at !== null && $coupon->expires_at < Carbon::now()->toDateTimeString()) {
            return response()->json(['coupon_code' => ['Coupon has expired.']], 422);
        }

        if ($invoice->total_discount > 0.00) {
            $invoice->subtotal_price = $invoice->subtotal_price + $invoice->total_discount; // revert discounted subtotal
        }

        if ($coupon->is_percent === true) {
            $discount = $invoice->subtotal_price * ($coupon->amount / 100);
        } else {
            $discount = $coupon->amount;
        }
        $discount = round($discount, 2);

        if ($discount > $invoice->subtotal_price) {
            $discount = $invoice->subtotal_price;
        }

        $invoice->total_discount = $discount;
        $invoice->coupon_code = $couponCode;
        $invoice->subtotal_price = $invoice->subtotal_price - $invoice->total_discount; // apply discount to subtotal because that is how it all works in core
        $invoice->save();
        return response()->json($invoice);
    }
}
