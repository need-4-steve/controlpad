<?php

namespace App\Services\Cart;

use App\Repositories\Eloquent\ItemRepository;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\MediaRepository;
use App\Repositories\Eloquent\UserRepository;
use Carbon\Carbon;
use CPCommon\Pid\Pid;

/**
 * Methods to deal with a wholesale cart
 */
class CustomCartService
{
    protected $itemRepo;
    protected $cartRepo;
    protected $userRepo;
    protected $mediaRepo;

    public function __construct(ItemRepository $itemRepo, ?CartRepository $cartRepo, UserRepository $userRepo, MediaRepository $mediaRepo)
    {
        $this->itemRepo = $itemRepo;
        $this->cartRepo = $cartRepo;
        $this->userRepo = $userRepo;
        $this->mediaRepo = $mediaRepo;
    }

    public function processNewCartlines($itemsWithQuantity, $cartType)
    {
        $cart = $this->cartRepo->show(null, $cartType);
        if (auth()->user()->hasRole(['Superadmin', 'Admin']) || $cartType === 'custom_corp') {
            $userId = config('site.apex_user_id');
        } else {
            $userId = auth()->user()->id;
        }

        $itemIds = collect($itemsWithQuantity)->pluck('item_id');
        $cartlines = $this->processItems($cart->id, $userId, $itemsWithQuantity, $itemIds);
        return $this->cartRepo->addItem($cartlines, $cart, $itemIds, $cart->type);
    }

    public function processItems($cartId, $userId, $itemsWithQuantity, $itemIds)
    {
        $itemsWithPrice = $this->itemRepo->getItemsWithRetailPrice($itemIds, $userId, $userId);
        $newLines = $this->mergePriceWithQuantity($itemsWithQuantity, $itemsWithPrice);
        return $this->cleanItemsForInsertion($newLines->toArray(), $cartId, $userId);
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

    private function cleanItemsForInsertion(array $items, $cartId, $userId)
    {
        $cartlines = [];
        $userPid = $this->userRepo->getPidForUserId($userId);

        $media = $this->mediaRepo->getMediaForItems($items);

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
                'inventory_owner_id' => $userId,
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
}
