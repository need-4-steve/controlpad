<?php namespace Test\MockServices;

use CPCommon\Pid\Pid;
use App\Checkout;

class MockInventoryService implements \App\Services\InventoryServiceInterface
{
    public const MIN_MAX_ITEM = [
        'id' => 1,
        'user_pid' => '1',
        'owner_pid' => '1',
        'wholesale_price' => 2.00,
        'retail_price' => 3.00,
        'premium_price' => 3.50,
        'inventory_id' => 1,
        'inventory_price' => 2.50,
        'premium_shipping_cost' => null,
        'disabled' => false,
        'sku' => '111111111',
        'option' => 'S',
        'variant' => [
            'min' => null,
            'max' => null,
            'name' => 'Variant Name',
            'option_label' => 'Size',
            'product' => [
                'name' => 'Min Max Product',
                'min' => 10,
                'max' => 20,
                'tax_class' => null,
            ]
        ]
    ];

    public const NO_MIN_MAX_ITEM = [
        'id' => 2,
        'user_pid' => '1',
        'owner_pid' => '1',
        'wholesale_price' => 2.22,
        'retail_price' => 3.33,
        'premium_price' => 3.55,
        'inventory_price' => 2.99,
        'inventory_id' => 2,
        'premium_shipping_cost' => null,
        'disabled' => false,
        'sku' => '222222222',
        'option' => 'M',
        'variant' => [
            'min' => null,
            'max' => null,
            'name' => 'Variant Name',
            'option_label' => 'Size',
            'product' => [
                'name' => 'No Min Max Product',
                'min' => null,
                'max' => null,
                'tax_class' => null,
            ]
        ]
    ];

    public const NO_INV_PRICE_ITEM = [
        'id' => 3,
        'user_pid' => '1',
        'owner_pid' => '1',
        'wholesale_price' => 2.33,
        'retail_price' => 3.44,
        'premium_price' => 3.88,
        'inventory_price' => null,
        'inventory_id' => 3,
        'premium_shipping_cost' => null,
        'disabled' => false,
        'sku' => '333333333',
        'option' => 'L',
        'variant' => [
            'min' => null,
            'max' => null,
            'name' => 'Variant Name',
            'option_label' => 'Size',
            'product' => [
                'name' => 'No Inv Price Product',
                'min' => null,
                'max' => null,
                'tax_class' => null,
            ]
        ]
    ];

    public const DISABLED_ITEM = [
        'id' => 4,
        'user_pid' => '1',
        'owner_pid' => '1',
        'wholesale_price' => 2.44,
        'retail_price' => 3.66,
        'premium_price' => 4.10,
        'inventory_id' => 4,
        'inventory_price' => null,
        'premium_shipping_cost' => null,
        'disabled' => true,
        'sku' => '4444444444',
        'option' => 'XL',
        'variant' => [
            'min' => null,
            'max' => null,
            'name' => 'Variant Name',
            'option_label' => 'Size',
            'product' => [
                'name' => 'Disabled Product',
                'min' => null,
                'max' => null,
                'tax_class' => null,
            ]
        ]
    ];

    public const BUNDLE_ITEM = [
        'id' => 5,
        'user_pid' => '1',
        'owner_pid' => '1',
        'wholesale_price' => 2.77,
        'retail_price' => 3.99,
        'premium_price' => 4.30,
        'inventory_price' => null,
        'inventory_id' => 5,
        'premium_shipping_cost' => null,
        'disabled' => true,
        'sku' => '555555555',
        'option' => 'XXL',
        'quantity' => 10,
        'variant' => [
            'min' => null,
            'max' => null,
            'name' => 'Variant Name',
            'option_label' => 'Size',
            'product' => [
                'name' => 'Disabled Product',
                'min' => null,
                'max' => null,
                'tax_class' => null,
            ]
        ]
    ];

    public const ITEMS = [
        MockInventoryService::MIN_MAX_ITEM,
        MockInventoryService::NO_MIN_MAX_ITEM,
        MockInventoryService::NO_INV_PRICE_ITEM,
        MockInventoryService::DISABLED_ITEM
    ];

    public const BUNDLE = [
        'id' => 1,
        'user_pid' => '1',
        'name' => 'Bundle Name',
        'wholesale_price' => 20.00,
        'items' => [MockInventoryService::BUNDLE_ITEM],
        'tax_class' => null,
    ];

    public const BUNDLES = [
        MockInventoryService::BUNDLE
    ];

    public function getInventories($itemIds, $userPid)
    {
        $resultItems = ['data' => []];
        foreach (MockInventoryService::ITEMS as $item) {
            if (in_array($item['id'], $itemIds)) {
                $item['user_pid'] = $userPid;
                $item['owner_pid'] = $userPid;
                $resultItems['data'][] = $item;
            }
        }
        return json_decode(json_encode($resultItems));
    }

    public function getBundles($bundleIds, $userPid)
    {
        $resultItems = ['data' => []];
        foreach (MockInventoryService::BUNDLES as $bundle) {
            if (in_array($bundle['id'], $bundleIds)) {
                $resultItems['data'][] = $bundle;
            }
        }
        return json_decode(json_encode($resultItems));
    }

    public function reserveInventoryForCheckout(Checkout $checkout, $partialReserve)
    {
        if (!empty($checkout->transfer_pid)) {
            return refreshReservationForCheckout($checkout, $partialReserve);
        } else {
            return createReservationForCheckout($checkout, $partialReserve);
        }
    }
// TODO set up data/responses for testing cases
    public function createReservationForCheckout(Checkout $checkout, $partialReserve)
    {
        return (object) ['pid' => Pid::create()]; // TODO create results based on line items
    }

    public function refreshReservationForCheckout(Checkout $checkout, $partialReserve)
    {
        return (object) ['pid' => $checkout->transfer_pid];
    }

    public function cancelReservation($transferPid)
    {
        // TODO implement
    }

    public function transferReservation($transferPid, $userId, $userPid)
    {
        // TODO implement
    }
}
