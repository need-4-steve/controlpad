<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\AddressRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\InventoryRepository;
use App\Repositories\Eloquent\InvoiceRepository;
use App\Repositories\Eloquent\ItemRepository;
use App\Repositories\Eloquent\ShippingRateRepository;
use App\Services\PayMan\PayManService;
use App\Services\Orders\OrderService;
use App\Services\Inventory\InventoryService;
use App\Services\Commission\CommissionService;
use App\Services\CustomOrder\CustomOrderService;
use App\Services\Shippo\ShippingServiceExport;
use App\Services\Tax\TaxService;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Cartline;
use App\Models\CashType;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\User;
use App\Http\Requests\CustomOrderPayRequest;
use App\Http\Requests\CustomOrderTaxRequest;

class CustomOrderController extends Controller
{
    public function __construct(
        AuthRepository $authRepo,
        UserRepository $userRepo,
        AddressRepository $addressRepo,
        ItemRepository $itemRepo,
        CartRepository $cartRepo,
        PayManService $payMan,
        OrderService $orderService,
        InventoryService $inventoryService,
        InventoryRepository $inventoryRepo,
        InvoiceRepository $invoiceRepo,
        ShippingRateRepository $shippingRateRepo,
        TaxService $taxService,
        CommissionService $commissionService,
        ShippingServiceExport $shippingServiceExport
    ) {
        $this->authRepo = $authRepo;
        $this->userRepo = $userRepo;
        $this->addressRepo = $addressRepo;
        $this->cartRepo = $cartRepo;
        $this->paymentManager = $payMan;
        $this->orderService = $orderService;
        $this->inventoryService = $inventoryService;
        $this->inventoryRepo = $inventoryRepo;
        $this->itemRepo = $itemRepo;
        $this->shippingRateRepo = $shippingRateRepo;
        $this->taxService = $taxService;
        $this->invoiceRepo = $invoiceRepo;
        $this->commissionService = $commissionService;
        $this->shippingServiceExport = $shippingServiceExport;
        $this->globalSettings = app('globalSettings');
    }

    /**
     * @param $request -
     *     Address ['addresses']['billing']
     *     Address ['addresses']['shipping']
     *     float ['cart']['subtotal_price']
     */
    public function tax(CustomOrderTaxRequest $request)
    {
        $invoice = null;
        if (isset($request['token'])) {
            $invoice = $this->invoiceRepo->find($request['token']);
        }
        $taxInvoice = $this->calculateTaxes($request->all(), $invoice);
        if (isset($taxInvoice->error)) {
            return response($taxInvoice->error, 500);
        }

        // Temporarily support the old variable
        $taxInvoice->total_tax_amount = $taxInvoice->tax;

        return response()->json($taxInvoice, 200);
    }

    /**
     * @param $request -
     *     Address ['addresses']['billing']
     *     Address ['addresses']['shipping']
     *     float ['cart']['subtotal_price']
     */
    private function calculateTaxes($request, $invoice = null)
    {
        if (!$this->globalSettings->getGlobal('tax_calculation', 'show')) {
            return (object)['tax' => 0];
        }
        if (isset($request['cart']['tax_not_charged']) && $request['cart']['tax_not_charged']) {
            return (object)['tax' => 0];
        }
        $cart = new Cart($request['cart']);
        foreach ($request['items'] as $requestItem) {
            if (isset($request['taxable_type']) && $request['taxable_type'] === 'wholesale') {
                $item = $this->itemRepo->find($requestItem['item_id']);
                $requestItem['price'] = $item->wholesalePrice->price;
            }
            $cart['lines'][] = new Cartline($requestItem);
        }

        if (isset($invoice) && $invoice->type_id === 9) {
            $storeOwner = User::with('businessAddress')
                ->where('id', config('site.apex_user_id'))
                ->first();
        } elseif (isset($invoice)) {
            $storeOwner = User::with('businessAddress')
                ->where('id', $invoice->store_owner_user_id)
                ->first();
        } else {
            $storeOwner = $this->authRepo->getOwner();
        }

        if (isset($request['taxable_type']) && $request['taxable_type'] === 'wholesale') {
            $cart['total_discount'] = 0;
        }

        return $this->taxService->createCartTaxInvoice(
            $request['addresses']['billing'],
            $request['addresses']['shipping'],
            $storeOwner->businessAddress,
            $cart,
            'sale',
            $storeOwner->pid,
            false
        );
    }
}
