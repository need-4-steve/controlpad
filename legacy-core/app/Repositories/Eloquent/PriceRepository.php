<?php

namespace App\Repositories\Eloquent;

use App\Models\Price;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;
use App\Models\Inventory;
use App\Models\Item;

class PriceRepository
{
    use CommonCrudTrait;

    /**
     * Create a new instances of Price
     *
     * @param array $inputs
     * @return bool|Price
     */
    public function create($priceable_id, $priceType, $price, $class)
    {
        $price = Price::create([
            'price_type_id' => $priceType,
            'price' => $price,
            'priceable_type' => $class,
            'priceable_id' => $priceable_id
        ]);
        return $price;
    }

    /**
     * Update an instances of Price
     *
     * @param Price $price
     * @param array $inputs
     * @return bool|Price
     */
    public function update($priceable_id, $priceType, $amount, $class)
    {
        $price = Price::where('price_type_id', $priceType)->where('priceable_type', $class)->where('priceable_id', $priceable_id)->first();

        $price->price = $amount;
        $price->save();
        return $price;
    }

    /**
     * Updates premium price on a fulfilled by corporate product.
     *
     * @param User $authUser
     * @param double $price
     * @param double $itemId
     * @return Price $price
     */
    public function updatePremium($user, $newPrice, $itemId)
    {
        $inventory = Inventory::where('item_id', $itemId)->where('user_id', config('site.apex_user_id'))->first();
        if (!$inventory) {
            return ['error' => 'Could not find inventory for item.', 'status' => HTTP_BAD_REQUEST];
        }
        if ($user->hasRole(['Admin', 'Superadmin']) || $inventory->owner_id === $user->id) {
            $price = Price::where('priceable_id', $itemId)->where('priceable_type', Item::class)->where('price_type_id', 3)->first();
            if (!$price) {
                return ['error' => 'Could not find price for item.', 'status' => HTTP_BAD_REQUEST];
            }
            $price->price = $newPrice;
            $price->save();
            return $price;
        }
        return ['error' => 'Unauthorized', 'status' => HTTP_UNAUTHORIZED];
    }
}
