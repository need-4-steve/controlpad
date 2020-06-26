<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Requests\ShippingRateCreateRequest;
use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use App\Repositories\Eloquent\ShippingRateRepository;
use App\Repositories\Eloquent\AuthRepository;

class ShippingRateController extends Controller
{
    protected $authRepo;
    protected $shippingRateRepo;

    public function __construct(ShippingRateRepository $shippingRateRepo, AuthRepository $authRepo)
    {
        $this->shippingRateRepo = $shippingRateRepo;
        $this->authRepo = $authRepo;
    }

    public function index()
    {
        $user = $this->authRepo->getOwner();
        return $this->shippingRateRepo->index($user);
    }
    public function indexWholesale()
    {
        $user = $this->authRepo->getOwner();
        return $this->shippingRateRepo->indexWholesale($user);
    }

    public function create(ShippingRateCreateRequest $request)
    {
        $user = $this->authRepo->getOwner();
        return $this->shippingRateRepo->create($request, $user);
    }

    public function shippingCost()
    {
        $totalPrice = request()->all();
        if ($store_owner = session('store_owner')) {
            $amount = $this->shippingRateRepo->findPriceForUser($store_owner->id, $totalPrice);
        } else {
            $amount = $this->shippingRateRepo->findPriceForUser(config('site.apex_user_id'), $totalPrice);
        }

        return response()->json(['error' => false, 'message' => 'Success', 'data' => $amount], 200);
    }

    public function shippingCostByAuth()
    {
        $request = request()->all();
        $amount = $this->shippingRateRepo->findPriceForUser(auth()->id(), $request['total_price']);
        return response()->json($amount, 200);
    }
}
