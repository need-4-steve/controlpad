<?php

namespace App\Services\Cart;

use App\Repositories\Eloquent\ItemRepository;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\MediaRepository;
use Carbon\Carbon;
use CPCommon\Pid\Pid;

/**
 * Methods to deal with a wholesale cart
 */
class WholesaleCartService
{
    protected $itemRepo;
    protected $cartRepo;
    protected $mediaRepo;
    protected $userRepo;

    public function __construct(ItemRepository $itemRepo, CartRepository $cartRepo, UserRepository $userRepo, MediaRepository $mediaRepo)
    {
        $this->itemRepo = $itemRepo;
        $this->cartRepo = $cartRepo;
        $this->userRepo = $userRepo;
        $this->mediaRepo = $mediaRepo;
        $this->settings = app('globalSettings');
    }

    public function processNewCartlines($itemsWithQuantity)
    {
        $cart = $this->cartRepo->show();
        $itemIds = collect($itemsWithQuantity)->pluck('item_id');
        $ownerId = config('site.apex_user_id');
        $itemsWithPrice = $this->itemRepo->getItemsWithWholesalePrice($itemIds, $ownerId, $ownerId);
        $newLines = $this->mergePriceWithQuantity($itemsWithQuantity, $itemsWithPrice);
        // validate the quantity doesn't go outside purchase min/maxes
        $validation = $this->validateMinMax($newLines);
        if ($validation !== true) {
            return $validation;
        };
        $newLines = $this->cleanItemsForInsertion($newLines->toArray(), $cart->id, $ownerId);
        return $this->cartRepo->addItem($newLines, $cart, $itemIds);
    }

    private function mergePriceWithQuantity($itemsWithQuantity, $itemsWithPrice)
    {
        $quantities = [];
        $newLines = [];
        foreach ($itemsWithQuantity as $item) {
            $quantities[$item['item_id']] = $item['quantity'];
        }
        foreach ($itemsWithPrice as $item) {
            $item['quantity'] = $quantities[$item['id']];
        }
        return $itemsWithPrice;
    }

    private function validateMinMax($items)
    {
        $error = [];
        $variantCheck = [];
        foreach ($items as $item) {
            if (isset($item->min) && $item->quantity < $item->min) {
                $error[$item['id']] = ['You must purchase a minimum of ' . $item->min];
            }
            if (isset($item->max) && $item->quantity > $item->max && $item->max > 0) {
                $error[$item['id']] = ['You cannot purchase more than ' . $item->max];
            }
            if (isset($item->variant_id)) {
                $variantCheck[$item->variant_id]['id'] = $item->variant_id;
                $variantCheck[$item->variant_id]['name'] = $item->variant_name;
                if (!isset($variantCheck[$item->variant_id]['quantity'])) {
                    $variantCheck[$item->variant_id]['quantity'] = $item->quantity;
                } else {
                    $variantCheck[$item->variant_id]['quantity'] += $item->quantity;
                }
                if (isset($item->variant_min)) {
                    $variantCheck[$item->variant_id]['min'] = $item->variant_min;
                }
                if (isset($item->variant_max)) {
                    $variantCheck[$item->variant_id]['max'] = $item->variant_max;
                }
            }
        }
        foreach ($variantCheck as $variant) {
            if (isset($variant['min']) && $variant['min'] > $variant['quantity']) {
                $error['variant_error'] = 'You must purchase a total minimum of ' . $variant['min'];
            }
            if (isset($variant['max']) && $variant['max'] < $variant['quantity']) {
                $error['variant_error'] = 'You cannot purchase more than a total of ' . $variant['max'];
            }
        }
        if (count($error) > 0) {
            return $error;
        }
        return true;
    }

    private function cleanItemsForInsertion(array $items, $cartId, $ownerId)
    {

        $userPid = $this->userRepo->getPidForUserId($ownerId);

        $media = $this->mediaRepo->getMediaForItems($items);

        $cartlines = [];
        $mediaUrl = null;
        foreach ($items as $item) {
            if (isset($media['variant_media'][$item['variant_id']])) {
                $mediaUrl = $media['variant_media'][$item['variant_id']]['url'];
            } elseif (isset($media['product_media'][$item['product_id']])) {
                $mediaUrl = $media['product_media'][$item['product_id']]['url'];
            } else {
                $mediaUrl = null;
            }
            $cartlines[] = [
                'pid' => Pid::create(),
                'item_id' => $item['id'],
                'quantity' => $item['quantity'],
                'cart_id' => $cartId,
                'price' => $item['price'],
                'inventory_owner_id' => $ownerId,
                'inventory_owner_pid' => $userPid,
                'tax_class' => $item['tax_class'],
                'items' => json_encode([
                    [
                        'id' => $item['id'],
                        'inventory_id' => $item['inventory_id'],
                        'product_name' => $item['product_name'],
                        'variant_name' => $item['variant_name'],
                        'option_label' => $item['variant_option_label'],
                        'option' => $item['option'],
                        'sku' => $item['sku'],
                        'premium_shipping_cost' => $item['premium_shipping_cost'],
                        'img_url' => $mediaUrl,
                        'weight' => $item['weight'],
                        'variant_label' => $item['variant_label']
                    ]
                ]),
                'created_at'  => Carbon::now()->toDateTimeString(),
            ];
        }
        return $cartlines;
    }

    public function checkMinAmount($cart)
    {
        $wholesaleMin = $this->settings->getGlobal('wholesale_cart_min', 'show');
        $type = $this->settings->getGlobal('wholesale_cart_min', 'value');
        $amount = floatval($this->settings->getGlobal('wholesale_cart_min_amount', 'value'));
        if ($wholesaleMin && $type === 'dollar' && $amount > $cart->subtotal_price
        ) {
            return [
                'error' => true,
                'message'=>'Minimum order amount is $'. $this->settings->getGlobal('wholesale_cart_min_amount', 'value') . '.  Please add more items to your cart.'];
        }
        if ($wholesaleMin && $type === 'quantity'
        ) {
            $count = 0;
            foreach ($cart->lines as $lines) {
                $count = $lines->quantity + $count;
            }
            if ($count < $amount) {
                return [
                    'error' => true,
                    'message' => 'Minimum order quantity is '. $this->settings->getGlobal('wholesale_cart_min_amount', 'value')
                    . ' items.  Please add more items to your cart.'];
            }
        }
        return ['error' => false];
    }
}
