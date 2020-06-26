<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\EloquentV0\CouponRepository;
use App\Services\SettingsServiceInterface;

class CouponController extends Controller
{

    private $couponRepo;
    private $settingsService;

    public function __construct(
        SettingsServiceInterface $settingsService
    ) {
        $this->couponRepo = new CouponRepository;
        $this->settingsService = $settingsService;
    }

    public function index(Request $request)
    {
        $this->validate($request, Coupon::$indexRules);
        $request = $this->determineSortByOrder($request);
        $params = $request->only(Coupon::$indexParams);
        // Check/Set owner_pid
        $isAdmin = $request->user->hasRole(['Superadmin','Admin']);
        if (!$isAdmin) {
            $params['owner_pid'] = $request->user->pid;
        }

        return response()->json($this->couponRepo->index($params));
    }

    public function show(Request $request, $id)
    {
        $coupon = $this->couponRepo->couponById($id);
        if (!$coupon) {
            abort(404, 'Coupon not found');
        }
        if (!$request->user->hasRole(['Superadmin', 'Admin']) && $coupon->owner_pid != $request->user->pid) {
            abort(403, 'Admin or owner only');
        }

        return response()->json($coupon);
    }

    public function create(Request $request)
    {
        $this->validate($request, Coupon::$createRules);
        $coupon = $request->only(array_keys(Coupon::$createRules));
        $isAdmin = $request->user->hasRole(['Superadmin', 'Admin']);
        // Wholesale is admin only
        if ($coupon['type'] == 'wholesale' && !$isAdmin) {
            abort(403, 'wholesale coupons are admin only');
        }
        if (!$isAdmin) {
            $coupon['owner_pid'] = $request->user->pid;
            $coupon['owner_id'] = $request->user->id;
        } else {
            if (!isset($coupon['owner_pid'])) {
                $settings = $this->settingsService->getSettings(['company_pid']);
                $coupon['owner_pid'] = $settings->company_pid->value;
            }
            if (!isset($coupon['owner_id'])) {
                $coupon['owner_id'] = 1;
            }
        }
        if (!$this->couponRepo->isCodeAvailable($coupon['code'], $coupon['owner_pid'])) {
            return response()->json(['code' => ['Coupon code already exists']]);
        }
        $coupon = $this->couponRepo->create($coupon);
        event(new \CPCommon\Events\GenericEvent(
            'coupon-created',
            [
                'coupon' => $coupon
            ],
            $request->user->orgId,
            0
        ));
        return response()->json($coupon, 201);
    }

    public function delete(Request $request, $id)
    {
        $coupon = $this->couponRepo->couponById($id);
        if ($coupon == null) {
            return response(''); // If coupon doesn't exist then it is deleted
        } elseif (!$request->user->hasRole(['Superadmin', 'Admin']) && $coupon->owner_pid != $request->user->pid) {
            // Not owner or admin is forbidden
            abort(403, 'Admin or Owner only');
        }
        $coupon->delete();
        event(new \CPCommon\Events\GenericEvent(
            'coupon-deleted',
            [
                'coupon_id' => $coupon->id,
                'coupon' => $coupon
            ],
            $request->user->orgId,
            0
        ));
        return response('');
    }

    private function determineSortByOrder(Request $request) : Request
    {
        if ($request->get('sort_by')) {
            if (strpos($request->get('sort_by'), '-') === 0) {
                $request->merge(['sort_by' => str_replace('-', '', $request['sort_by'])]);
                $request->merge(['in_order' => 'desc']);
            } elseif (!$request->has('in_order')) {
                $request->merge(['in_order' => 'asc']);
            }
        }
        return $request;
    }
}
