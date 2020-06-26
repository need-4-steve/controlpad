<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\CouponCreateRequest;
use App\Http\Requests\CouponUpdateRequest;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\CouponRepository;
use App\Repositories\Eloquent\CouponAppliedRepository;
use App\Repositories\Eloquent\CartRepository;
use App\Services\Authentication\JWTAuthService;
use App\Models\Cart;

class CouponController extends Controller
{
    /* @var \App\Repositories\Eloquent\CouponRepository */
    protected $couponRepo;
    protected $couponAppliedRepo;
    protected $cartRepo;

    public function __construct(
        AuthRepository $authRepo,
        CartRepository $cartRepo,
        CouponAppliedRepository $couponAppliedRepo,
        CouponRepository $couponRepo
    ) {
        $this->authRepo = $authRepo;
        $this->couponRepo = $couponRepo;
        $this->couponAppliedRepo = $couponAppliedRepo;
        $this->cartRepo = $cartRepo;
        $this->globalSettings = app('globalSettings');
    }

    public function index()
    {
        $request = request()->all();
        return response()->json($this->couponRepo->index($request), 200);
    }

    public function show($id)
    {
        $coupon = $this->couponRepo->find($id);

        if (! $coupon) {
            return response()->json(['That coupon does not exist.'], 400);
        }

        return response()->json($coupon, 200);
    }

    public function store(CouponCreateRequest $request)
    {
        $inputs =  $request->all();
        $claims = JWTAuthService::verify(session()->get('cp_token'));
        if (isset($claims['actualUserId']) && $claims['actualUserId'] !== null) {
            return response()->json(['error'=>true, 'message'=>'You are login as a ' .$this->globalSettings->getGlobal('title_rep', 'value')], 422);
        }
        $inputs['owner_id'] = $this->authRepo->getOwnerId();

        if ($inputs['amount'] < 0) {
            return response()->json(['error'=>true, 'message'=>'Cannot create coupon with negtive amount'], 422);
        }
        if ($inputs['is_percent'] && $inputs['amount'] > 100) {
            return response()->json(['error'=>true, 'message'=>'Cannot create coupon with more than 100% discount'], 422);
        }
        if ($inputs['max_uses'] < 1) {
            return response()->json(['error'=>true, 'message'=>'Number of uses cannot be less than 1'], 422);
        }

        $existingCoupon = $this->couponRepo->findByCodeForUser($inputs['code'], $inputs['owner_id']);
        if ($existingCoupon) {
            return response()->json(['Coupon code already exists'], 422);
        }

        $coupon = $this->couponRepo->create($inputs);

        if (! $coupon) {
            return response()->json(['There was a problem creating your coupon'], 412);
        }

        return response()->json($coupon, 200);
    }

    public function destroy($id = null)
    {
        $coupon = $this->couponRepo->find($id);

        if (! $coupon) {
            return response()->json(['That coupon does not exist.'], 400);
        }

        if (! $this->couponRepo->delete($coupon)) {
            return response()->json(['There was a problem deleting your coupon'], 500);
        }

        return response()->json([$this->messages['Success']], 200);
    }

    public function apply($couponCode)
    {
        $taxRateRequest = request()->all();
        if ($taxRateRequest === []) {
            $taxRateRequest = null;
        }
        $couponType = request()->input('cart_type', 'retail');
        if ($couponType === 'custom_corp' || $couponType === 'custom_personal') {
            $cart = $this->cartRepo->show(null, $couponType);
        } else {
            $cart = $this->cartRepo->show();
        }

        $owner = $this->authRepo->getStoreOwner();
        if ($owner->id === config('site.apex_user_id') || ($owner->hasSellerType(['Affiliate']) || $couponType === 'custom_corp')) {
            $ownerId = config('site.apex_user_id');
        } else {
            $ownerId = $owner->id;
        }
        $coupon = $this->couponAppliedRepo->attach($cart, $ownerId, $couponCode, $couponType);
        if (isset($coupon['error'])) {
            session()->put('cart', $this->cartRepo->find($cart->uid)
                ->load(
                    'lines.item.product.type',
                    'coupons',
                    'shipping',
                    'bundles'
                ));
            return response()->json([$coupon], 400);
        }
        // Saving new field for checkout api
        $cart->coupon_id = $coupon->id; // This will be saved during updateTotals
        // End checkout api data

        if ($couponType === 'custom_corp' || $couponType === 'custom_personal') {
            $cart = $this->cartRepo->updateTotals($cart, $taxRateRequest, $couponType);
        } else {
            $cart = $this->cartRepo->updateTotals($cart, $taxRateRequest);
        }
        session()->put('cart', $cart);

        $cart = $cart->toArray();
        return response()->json($cart, 200);
    }

    /**
    * This is for showing the orders that a coupon was used on
    * @param $coupon_id integer
    * @return Response Json
    */
    public function showCoupon($coupon_id)
    {
        $coupon = $this->couponRepo->showCoupon($coupon_id);
        return response()->json($coupon, 200);
    }

    public function downloadCsvCoupon()
    {
        $request = request()->all();
        $filename = 'coupon.csv';
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header("Cache-control: private");
        header("Content-type: application/force-download");
        header("Content-transfer-encoding: binary\n");

        $out = fopen('php://output', 'w');

        $couponData = [
            'name',
            'code',
            'amount',
            'used',
            'max_uses',
            'owner_id',
            'expires_at',
            'type'
        ];
        $coupons = $this->couponRepo->index($request, false);
        $maxCount = 0;
        $couponList = [];

        foreach ($coupons as $coupon) {
            if ($coupon->is_percent) {
                $amount = $coupon->amount . "%";
            } else {
                $amount =  "$" . $coupon->amount;
            }
            $couponList [] = [
                $coupon->title,
                $coupon->code,
                $amount,
                $coupon->uses,
                $coupon->max_uses,
                $coupon->owner_id,
                isset($coupon->expires_at) ? $coupon->expires_at : " ",
                $coupon->type
            ];
        }
        fputcsv($out, $couponData);

        foreach ($couponList as $coupon) {
            fputcsv($out, $coupon);
        }
        fclose($out);
    }

    public function downloadAppliedCoupons()
    {
        $request = request()->all();
        $request['expands'] = ['orders'];
        $filename = 'applied_coupons.csv';
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header("Cache-control: private");
        header("Content-type: application/force-download");
        header("Content-transfer-encoding: binary\n");

        $out = fopen('php://output', 'w');

        $couponData = [
            'coupon_code',
            'coupon_name',
            'coupon_owner_id',
            'coupon_type',
            $this->globalSettings->getGlobal('title_rep', 'value') . '_store_id',
            'order_receipt_id',
            'order_customer_name',
            'order_total',
            'order_subtotal',
            'order_total_tax',
            'order_total_shipping',
            'order_total_discount',
            'coupon_amount',
            'order_date'


        ];
        $coupons = $this->couponRepo->index($request, false);
        $couponList = [];

        foreach ($coupons as $coupon) {
            foreach ($coupon->orders as $order) {
                if ($coupon->is_percent) {
                    $amount = $coupon->amount . "%";
                } else {
                    $amount =  "$" . $coupon->amount;
                }
                if ($coupon->type) {
                    $coupon_type = 'wholesale';
                } else {
                    $coupon_type = 'retail';
                }
                $couponList [] = [
                    $coupon->code,
                    $coupon->title,
                    $coupon->owner_id,
                    $coupon_type,
                    $order->store_owner_user_id,
                    $order->receipt_id,
                    $order->customer->first_name . " " . $order->customer->last_name,
                    $order->total_price,
                    $order->subtotal_price,
                    $order->total_tax,
                    $order->total_shipping,
                    $order->total_discount,
                    $amount,
                    $order->created_at
                ];
            }
        }

        fputcsv($out, $couponData);

        foreach ($couponList as $coupon) {
            fputcsv($out, $coupon);
        }
        fclose($out);
    }
}
