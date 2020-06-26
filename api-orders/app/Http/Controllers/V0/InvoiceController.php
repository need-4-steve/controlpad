<?php

namespace App\Http\Controllers\V0;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Cart;
use App\Invoice;
use App\InvoiceItem;
use App\Note;
use App\Repositories\EloquentV0\CartRepository;
use App\Services\InventoryServiceInterface;
use App\Services\SettingsServiceInterface;
use App\Services\UserServiceInterface;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{

    private $inventoryService;
    private $settingsService;
    private $userService;

    public function __construct(
        InventoryServiceInterface $inventoryService,
        SettingsServiceInterface $settingsService,
        UserServiceInterface $userService
    ) {
        $this->cartRepo = new CartRepository;
        $this->inventoryService = $inventoryService;
        $this->settingsService = $settingsService;
        $this->userService = $userService;
    }

    public function showByToken(Request $request, $token)
    {
        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        $invoice = Invoice::where('token', $token)->withTrashed()->first();
        if ($invoice === null) {
            abort(404, 'Invoice missing');
        }
        if ($invoice->order_id !== null) {
            abort(422, json_encode(['message' => 'Invoice already paid']));
        }
        if ($invoice->isExpired() || $invoice->deleted_at !== null) {
            abort(422, json_encode(['message' => 'Invoice expired']));
        }

        $invoice->items = InvoiceItem::where('invoice_id', '=', $invoice->id)->get();

        return response()->json($invoice);
    }

    public function createFromCart(Request $request, $cartPid)
    {
        $this->validate(
            $request,
            [
                'customer' => 'required', 'customer.email' => 'required|email',
                'customer.first_name' => 'required', 'customer.last_name' => 'required',
                'shipping' => 'numeric|min:0', 'discount' => 'numeric|min:0'
            ]
        );
        $cart = $this->cartRepo->cartByPid($cartPid);
        if (!$cart) {
            abort(404, 'Cart not found');
        }
        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        if (!$isAdmin && $request->user->pid != $cart['seller_pid']) {
            abort(403, 'Seller or admin only');
        }
        if ($cart->isEmpty()) {
            abort(400, 'Cart empty');
        }
        if (!in_array($cart->type, ['custom-corp', 'custom-retail', 'custom-wholesale', 'custom-affiliate', 'rep-transfer'])) {
            abort(400, 'Custom orders only');
        }
        $data = $request->only(['discount', 'shipping', 'customer', 'note']);

        $overrideAllowed = $cart->isCustom();
        $settings = $this->settingsService->getSettings(['einvoice_expire_time']);
        $invoice = new Invoice;
        $invoice->token = time().str_random(20);
        $hours = $settings->einvoice_expire_time->value;
        if ($hours < 1) {
            $hours = 1;
        }
        $invoice->expires_at = Carbon::now()->setTimezone('UTC')->addHours($hours)->toDateTimeString();

        $seller = $this->userService->getUserByPid($cart->seller_pid);
        switch ($cart->type) {
            case 'rep-transfer':
            case 'custom-wholesale':
                // Rep should be manually selected as buyer and not attached as a customer
                $rep = $this->userService->getUserByPid($data['customer']['pid']);
                if ($rep == null || $rep->role_id !== 5) {
                    abort(400, json_encode(['message' => 'Customer must be a rep']));
                }
                $customer = (object)$data['customer'];
                $customer->id = $rep->id;
                $customer->role_id = $rep->role_id;
                break;
            default:
                $customer = $this->userService->createCustomer($seller->id, $data['customer']);
                break;
        }

        $invoice->customer_id = $customer->id;

        $invoice->store_owner_user_id = $seller->id;
        $invoice->type_id = $this->parseOrderType($cart->type, $seller, $customer);

        $invoice->subtotal_price = $cart->calculateSubtotal();
        if (isset($cart->coupon)) {
            $invoice->total_discount = $cart->coupon->calculateDiscount($invoice->subtotal_price);
            $invoice->coupon_code = $cart->coupon->code;
            $invoice->couponable = 0; // If an invoice is created with a coupon, prevent it from being overriden
        } else {
            $invoice->total_discount = ((isset($data['discount']) && $overrideAllowed) ? $data['discount'] : 0.00);
            $invoice->coupon_code = null;
            $invoice->couponable = 1;
        }
        if ($cart->type === 'rep-transfer') {
            $invoice->couponable = 0;
        }

        if (isset($data['shipping']) && $overrideAllowed) {
            $invoice->total_shipping = $data['shipping'];
            $invoice->shipping_rate_id = null;
        } else {
            $shippingService = app()->make(\App\Services\ShippingServiceInterface::class);
            $shippingRate = $shippingService->findRate($cart->inventory_user_pid, $cart->type, $invoice->subtotal_price);
            if ($shippingRate == null) {
                abort(501, 'Shipping rates have not been set.');
            }
            $invoice->shipping_rate_id = $shippingRate->id;
            $invoice->total_shipping = $shippingRate->amount + $cart->getPremiumShipping();
        }

        $inventoryTransfer = $this->inventoryService->createReservation($cart->lines, false);
        if (!empty($inventoryTransfer->errors) || empty($inventoryTransfer->reservations)) {
            return response()->json([
                'result_code' => 2,
                'result' => 'Inventory unavailable.',
                'message' => 'Some or all items not available.'
            ], 422);
        }
        try {
            $invoice->uid = '';
            $invoice->deleted_at = Carbon::now()->setTimezone('UTC')->toDateTimeString(); // Mark as deleted until inventory is transfered
            $invoice->save();
            $lines = [];
            foreach ($cart->lines as $line) {
                $item = [
                    'invoice_id' => $invoice->id,
                    'item_id' => $line->item_id,
                    'bundle_id' => $line->bundle_id,
                    'price' => $line->price,
                    'quantity' => $line->quantity,
                    'variant' => $line->items[0]->variant_name,
                    'option' => $line->items[0]->option
                ];
                $lines[] =  $item;
            }
            InvoiceItem::insert($lines);
            // create note
            if ($data['note']) {
                $noteCreate = new Note;
                $noteCreate->body = $data['note'];
                $noteCreate->user_id = $seller->id;
                $noteCreate->noteable_id = $invoice->id;
                $noteCreate->noteable_type = Invoice::class;
                $noteCreate->save();
            }
            usleep(25000); // Testing a suspected issue with data replication
            $this->inventoryService->transferReservation($inventoryTransfer->reservation_group_id, null, null);
            $invoice->deleted_at = null;
            $invoice->uid = "I" . strtoupper(str_random(5)) . '-' . $invoice->id;
            $invoice->save();
            $invoice->items = $lines;
        } catch (\Exception $e) {
            // Clean up invoice if inventory fails or other errors
            $invoice->forcedelete();
            throw $e;
        }

        $this->cartRepo->empty($cart->pid);
        return response()->json($invoice, 201);
    }

    public function cancelInvoiceList(Request $request)
    {
        // TODO
        // Iterate
        // Put inventories back, make sure type_id 9 is for corp
        // Delete invoice
    }

    private function parseOrderType($storeType, $seller, $buyer)
    {
        switch ($storeType) {
            case 'wholesale':
            case 'custom-wholesale':
                return 1;
            case 'retail':
            case 'custom-corp':
            case 'custom-retail':
                if (in_array($seller->role, ['Admin', 'Superadmin'])) {
                    if (in_array($buyer->role_id, [7, 8])) {
                        return 5;
                    } else {
                        return 2;
                    }
                }
                return 3;
            case 'custom-personal':
                return 10;
            case 'affiliate':
            case 'custom-affiliate':
                return 9;
            case 'rep-transfer':
                return 11;
        }
    }
}
