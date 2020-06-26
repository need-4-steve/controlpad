<?php

namespace App\Repositories\Eloquent;

use Carbon\Carbon;
use App\Models\Coupon;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;

class CouponAppliedRepository
{
    /**
     * Check coupon availability by limit
     *
     * @param $couponCode
     * @return mixed
     */
    public function checkAvailability($couponCode, $ownerId, $type)
    {
        switch ($type) {
            case 'custom_corp':
                $couponType = 'retail';
                break;
            case 'custom_personal':
                $couponType = 'retail';
                break;
            case 'wholesale':
                $couponType = 'wholesale';
                break;
            case 'retail':
                $couponType = 'retail';
                break;
            default:
                $couponType = 'wholesale';
        }

        $coupon = Coupon::where('code', $couponCode)
                    ->where('owner_id', $ownerId)
                    ->where('type', $couponType)
                    ->first();

        if (empty($coupon)) {
            return [
                'error' => "Could not find a valid coupon code."
            ];
        }

        if ($coupon->uses >= $coupon->max_uses) {
            return [
                'error' => "Coupon code has reached it's maximum uses."
            ];
        }
        if ($coupon->expires_at !== null && $coupon->expires_at < Carbon::now()->toDateTimeString()) {
            return[
                'error' => "Coupon has expired."
            ];
        }

        return $coupon;
    }

    /**
     * Check coupon availability by limit
     *
     * @param $couponCode
     * @return mixed
     */
    public function attach($object, $ownerId, $couponCode = null, $type = 'cart')
    {
        $coupon = $this->checkAvailability($couponCode, $ownerId, $type);
        if (isset($coupon['error'])) {
            $object->coupons()->detach();
            return $coupon;
        }
        $object->coupons()->sync([$coupon->id]);
        return $object->load('coupons');
    }

    public function attachOrder($object, $coupon)
    {
        $object->coupons()->sync([$coupon->id]);
    }

    /**
     * Adjust totals and discounts to the object
     *
     * @param
     * @return mixed
     */
    public function apply($object, $ownerId)
    {
        $coupon = $object->coupons()->first();

        if (!isset($coupon)) {
            return $object;
        }

        if ($coupon->owner_id !== $ownerId) {
            return $object;
        }

        if ($coupon->is_percent === true) {
            $discount = $object->subtotal_price * ($coupon->amount / 100);
        } else {
            $discount = $coupon->amount;
        }
        $discount = round($discount, 2);

        if ($discount > $object->subtotal_price) {
            $discount = $object->subtotal_price;
        }

        $object->subtotal_price -= $discount;
        $object->total_discount = $discount;
        $object->save();
        return $object;
    }

    /**
     * Calculates coupon discount on the TemplateStoreController cart.
     *
     * @param string $coupon
     * @param array $templateStoreCart
     * @return array $templateStoreCart
     */
    public function calculateDiscount($coupon, $templateStoreCart)
    {
        if ($coupon->is_percent === true) {
            $discount = $templateStoreCart['subtotal_price'] * ($coupon->amount / 100);
        } else {
            $discount = $coupon->amount;
        }
        $discount = round($discount, 2);

        if ($discount > $templateStoreCart['subtotal_price']) {
            $discount = $templateStoreCart['subtotal_price'];
        }

        $templateStoreCart['subtotal_price'] -= $discount;
        $templateStoreCart['total_discount'] = $discount;
        return $templateStoreCart;
    }
}
