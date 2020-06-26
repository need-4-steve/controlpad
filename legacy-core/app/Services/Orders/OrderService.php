<?php

namespace App\Services\Orders;

use App\Jobs\ProcessPayment;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderType;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\InventoryRepository;
use App\Repositories\Eloquent\ItemRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\OrderlineRepository;
use App\Repositories\Eloquent\ShippingRateRepository;
use App\Services\Commission\CommissionService;
use App\Services\PayMan\PayManService;
use App\Services\Shippo\ShippingService;
use DB;

class OrderService
{
    protected $cartRepo;
    protected $commissionService;
    protected $inventoryRepo;
    protected $itemRepo;
    protected $orderRepo;
    protected $orderlineRepo;
    protected $shippingRate;

    public function __construct(
        CartRepository $cartRepo,
        CommissionService $commissionService,
        InventoryRepository $inventoryRepo,
        ItemRepository $itemRepo,
        OrderRepository $orderRepo,
        OrderlineRepository $orderlineRepo,
        PayManService $payMan,
        ShippingRateRepository $shippingRateRepo,
        ShippingService $shippingService
    ) {
        $this->cartRepo = $cartRepo;
        $this->commissionService = $commissionService;
        $this->inventoryRepo = $inventoryRepo;
        $this->itemRepo= $itemRepo;
        $this->orderRepo = $orderRepo;
        $this->orderlineRepo = $orderlineRepo;
        $this->paymentManager = $payMan;
        $this->shippingRateRepo = $shippingRateRepo;
        $this->shippingService = $shippingService;
    }

    public function makePayment()
    {
        $params = [
            'headers' => [
                'Authorization' => 'APIKey ' . Config::get('paymentManager.APIKey'),
            ]
        ];

        // TODO: Figure out what 'client' this is referring to
        $res = $client->get(config('paymentManager.url') . '/sale/cridit-card', $params);
        $result = json_decode($res->getBody(), 1);
    }

    public static function processPayment(Order $order, array $paymentData)
    {
        // TOTO: create job and listener(s) that go with this event
        dispatch(new ProcessPayment($order, $paymentData));
        $payments = [
            'pm' => self::$paymentManager->creditCard($paymentData)
        ];
        return $payments;
    }

    /**
     * Take an array of products and create an order from them.
     *
     * @param $cart
     * @param array $data
     */
    public function createOrder($storeOwnerId, $cart, array $data, $transaction = null, $orderTypeId = null)
    {
        $data['cart'] = $cart;
        if (!isset($data['cart']['total_shipping'])) {
            $shippingRate = $this->shippingRateRepo->findPriceForUser($storeOwnerId, $data['cart']['subtotal_price'], ($orderTypeId === 1 ? 'wholesale' : 'retail'));
            $cart['total_shipping'] = $shippingRate->amount;
            $cart['shipping_rate_id'] = $shippingRate->id;
        }

        if (!isset($orderTypeId)) {
            $order = $this->orderRepo->create($data, $storeOwnerId);
        } else {
            $order = $this->orderRepo->create($data, $storeOwnerId, $orderTypeId);
        }
        $order->receipt_id = $this->orderRepo->genReceiptID().'-'.$order->id;
        $order->confirmation_code = $order->receipt_id; // searchable code should keep the old receipt_id
        if ($transaction !== null) {
            $order->transaction_id = $transaction['id'];
            if (isset($transaction['gatewayReferenceId'])) {
                $order->gateway_reference_id = $transaction['gatewayReferenceId'];
            }
        }
        $order->total_discount = $cart->total_discount;
        if (isset($data['order_type']) && $data['order_type'] === 'cash') {
            $order->cash = true;
        }
        $order->tax_invoice_pid = $cart->tax_invoice_pid;

        $order->save();
        $orderline = $this->orderlineRepo->create($order, $cart['lines']);

        if ($orderline->lines->first() && $orderline->lines->first()->discount_type_id === 1) {
            $order->source = 'Facebook';
            $order->save();
        } elseif (isset($data['source'])) {
            $order->source = $data['source'];
            $order->save();
        } else {
            $order->source = 'Web';
            $order->save();
        };
        $this->orderlineRepo->createBundleOrderlines($order, $cart);
        $order->load(['lines', 'customer']);
        if (!isset($orderTypeId)) {
            $order = $this->checkOrderType($order);
        }
        $order = $this->checkFulfillment($order);
        return $order;
    }

    /**
     * Check and save to see if an order type is fulfilled by corporate or mixed.
     *
     * @param Order $order
     */
    private function checkOrderType($order)
    {
        if ($order->store_owner_user_id === config('site.apex_user_id')) {
            $orderType = OrderType::where('id', 6)->first(); // OrderType 6 = Fulfilled by Corporate
            $fulfilledByCorp = $order->lines->where('type', $orderType->name)->count();
            $notFulfilledByCorp = $order->lines->where('type', '!=', $orderType->name)->count();

            if ($fulfilledByCorp > 0 and $notFulfilledByCorp > 0) {
                $order->type_id = 7; // Order Type 7 = Mixed
                $order->save();
                return $order;
            }

            if ($fulfilledByCorp > 0) {
                $order->type_id = 6; // Order Type 6 = Fulfilled by Corporate
                $order->save();
            }
        }
        if ($order->store_owner_user_id !== config('site.apex_user_id') and
            $order->storeOwner->hasSellerType('Affiliate') and
            $order->lines->where('inventory_owner_id', config('site.apex_user_id'))->count() > 0) {
            $order->type_id = 9; // Order Type 9 = Affiliate
            $order->save();
        }
        return $order;
    }

    /**
     * Check to see if all bundles are fulfilled by corporate.
     * If so mark it as fulfilled because it doesn't need to be shipped.
     *
     * @param Order $order
     */
    private function checkFulfillment($order)
    {
        if ($order->totalBundles() > 0) {
            $fulfilledByCorp = 0;
            $notFulfilledByCorp = 0;
            foreach ($order->bundles as $bundle) {
                if ($bundle->bundle->type_id == 2) {
                    $fulfilledByCorp++;
                } else {
                    $notFulfilledByCorp++;
                }
            }
            $notFulfilledByCorp += $order->totalProducts();
            if ($fulfilledByCorp > 0 and $notFulfilledByCorp === 0) {
                $order->status = 'fulfilled';
                $order->save();
            }
        }
        return $order;
    }

    public function fulfilledByCorporatePayouts($cart, $storeOwnerId)
    {
        $payouts = null;
        foreach ($cart['lines'] as $line) {
            if ($line['inventory_owner_id'] === null) {
                $line['inventory_owner_id'] = $this->inventoryRepo->getItemOwnerId(session()->get('store_owner.id'), $line['item_id']);
            }
            if ($line['inventory_owner_id'] !== config('site.apex_user_id') and $cart['user_id'] !== $line['inventory_owner_id'] and $storeOwnerId !== $line['inventory_owner_id']) {
                $item = $this->itemRepo->find($line['item_id'])->load('product');
                if ($item->product->type_id === 5) {
                    $payouts[$line['inventory_owner_id']]['payeeUserId'] = $line['inventory_owner_id'];
                    if (!isset($payouts[$line['inventory_owner_id']]['amount'])) {
                        $payouts[$line['inventory_owner_id']]['amount'] = $line['price'] * $line['quantity'];
                    } else {
                        $payouts[$line['inventory_owner_id']]['amount'] += $line['price'] * $line['quantity'];
                    }
                }
            }
        }
        $payouts = (is_array($payouts)) ? array_values($payouts) : null;
        return $payouts;
    }

    public function fulfilledByCorpFilter($order)
    {
        $fulfilledByCorpLines = [];
        foreach ($order->lines as $key => $line) {
            if (isset($line->bundle) and $line->bundle->type_id === 2) {
                unset($order->lines[$key]);
                $fulfilledByCorpLines[] = $line;
            }
        }
        if (isset($fulfilledByCorpLines)) {
            $order->fulfilledByCorpLines = $fulfilledByCorpLines;
        }
    }

    public function addNewOrdersToProcessTable()
    {
        // Find most recent order id that is in process list
        $lastId = DB::table('order_process')->max('order_id');
        if ($lastId == null) {
            $lastId = 0;
        }
        // Copy new orders over
        DB::statement('INSERT INTO order_process(order_id, paid_at, tax_invoice_pid) SELECT id, paid_at, tax_invoice_pid FROM orders WHERE id > ? AND paid_at IS NOT NULL', [$lastId]);
    }
}
