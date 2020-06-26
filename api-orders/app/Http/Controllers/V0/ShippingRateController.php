<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ShippingServiceInterface;

class ShippingRateController extends Controller
{
    private $shippingService;

    public function __construct(
        ShippingServiceInterface $shippingService
    ) {
        $this->shippingService = $shippingService;
    }

    public function estimate(Request $request)
    {
        $this->validate(
            $request,
            [
                'seller_pid' => 'filled|required',
                'type' => 'required|in:retail,wholesale,custom-personal,custom-corp,custom-retail,affiliate',
                'amount' => 'required|numeric'
            ]
        );
        $shippingRate = $this->shippingService->findRate(
            $request->input('seller_pid'),
            $request->input('type'),
            round($request->input('amount'), 2)
        );
        if ($shippingRate == null) {
            abort(501, 'Shipping rates have not been set. Contact store owner to resolve this issue.');
        }
        return response()->json($shippingRate);
    }
}
