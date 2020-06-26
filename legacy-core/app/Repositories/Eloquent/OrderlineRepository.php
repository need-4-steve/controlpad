<?php

namespace App\Repositories\Eloquent;

use App\Models\Cartline;
use App\Models\Order;
use App\Models\Orderline;
use App\Models\User;
use App\Repositories\Contracts\OrderlineRepositoryContract;
use App\Repositories\Eloquent\InventoryRepository;
use DB;

class OrderlineRepository implements OrderlineRepositoryContract
{
    protected $inventoryRepo;

    public function __construct(InventoryRepository $inventoryRepo)
    {
        $this->inventoryRepo = $inventoryRepo;
    }

    /**
     * Create a new instances of Orderline
     *
     * @param  $cartlines
     * @return bool|Orderline
     */
    public function create(Order $order, $cartlines)
    {
        $orderline = [];
        DB::beginTransaction();
        $cartlines->load('item.variant');
        foreach ($cartlines as $cartline) {
            if (!isset($cartline->inventory_owner_id)) {
                $cartline->inventory_owner_id = $this->inventoryRepo->getItemOwnerId($order->store_owner_user_id, $cartline->item_id);
            }
            $orderline = [
                'pid'              => (isset($cartline->pid) ? $cartline->pid : null),
                'order_id'         => $order->id,
                'item_id'          => $cartline->item_id,
                'name'             => $cartline->item->product->name,
                'quantity'         => $cartline->quantity,
                'custom_sku'       => (isset($cartline->item->custom_sku) ? $cartline->item->custom_sku : null),
                'manufacturer_sku' => $cartline->item->manufacturer_sku,
                'price'            => $cartline->price,
                'type'             => $cartline->item->product->type->name,
                'inventory_owner_pid' => (isset($cartline->inventory_owner_pid) ? $cartline->inventory_owner_pid : null),
                'inventory_owner_id' => $cartline->inventory_owner_id,
                'variant'          => $cartline->item->variant->name,
                'option'           => $cartline->item->size,
                'event_id'         => $cartline->event_id,
                'items'            => (isset($cartline->items) ? $cartline->items : null)
            ];
            if (isset($cartline->discount) && $cartline->discount > 0) {
                $orderline['discount_amount'] = $cartline->discount;
                $orderline['discount_type_id'] = 1; // facekbook live sale
            }
            Orderline::create($orderline);
        }
        DB::commit();
        return $order->load('lines');
    }

    public function createBundleOrderlines(Order $order, $cart)
    {
        DB::beginTransaction();
        $bundles = $cart['bundles'];
        $companyUserId = config('site.apex_user_id');
        $companyUser = User::select('pid')->where('id', '=', $companyUserId)->first();
        if ($companyUser != null) {
            $companyUserPid = $companyUser->pid;
        } else {
            $companyUserPid = null;
        }
        foreach ($cart['bundles'] as $bundle) {
            // Add in data from checkout api if available
            $cartlineBundle = Cartline::select('pid', 'items')->where('cart_id', '=', $cart->id)->where('bundle_id', '=', $bundle->id)->first();
            // Create Orderline for the bundle.
            $orderline = [
                'pid'              => (isset($cartlineBundle->pid) ? $cartlineBundle->pid : null),
                'order_id'         => $order->id,
                'item_id'          => null,
                'bundle_id'        => $bundle->id,
                'bundle_name'      => $bundle->name,
                'name'             => $bundle->name,
                'quantity'         => $bundle->pivot->quantity,
                'custom_sku'       => null,
                'manufacturer_sku' => null,
                'price'            => $bundle->wholesalePrice->price,
                'type'             => "Bundle",
                'inventory_owner_id' => $companyUserId,
                'inventory_owner_pid' => $companyUserPid,
                'items' => (isset($cartlineBundle->items) ? $cartlineBundle->items : null)
            ];
            Orderline::create($orderline);
            foreach ($bundle['items'] as $item) {
                if (session()->has('store_owner')) {
                    $inventoryOwnerId = session()->get('store_owner')->id;
                } else {
                    $inventoryOwnerId = $this->inventoryRepo->getItemOwnerId(config('site.apex_user_id'), $item->id);
                }
                // Create Orderline for the each item in the bundle.
                $orderline = [
                    'order_id'         => $order->id,
                    'item_id'          => $item->id,
                    'bundle_id'        => $bundle->id,
                    'bundle_name'      => $bundle->name,
                    'name'             => $item->product->name,
                    'quantity'         => $item->pivot->quantity * $bundle->pivot->quantity,
                    'custom_sku'       => $item->custom_sku,
                    'manufacturer_sku' => $item->manufacturer_sku,
                    'price'            => 0,
                    'type'             => "Bundle",
                    'inventory_owner_id' => $inventoryOwnerId
                ];
                Orderline::create($orderline);
            }
        }
        DB::commit();
        return $order->load('lines');
    }

    /**
     * Update an instances of Orderline
     *
     * @param Orderline $orderline
     * @param array $inputs
     * @return bool|Orderline
     */
    public function update(Orderline $orderline, array $inputs = [])
    {
        //
    }
}
