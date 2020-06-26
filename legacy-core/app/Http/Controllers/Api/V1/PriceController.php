<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\PriceRepository;
use Validator;

class PriceController extends Controller
{

    public function __construct(PriceRepository $priceRepo)
    {
        $this->priceRepo = $priceRepo;
    }
    /**
     * Updates premium price on a fulfilled by corporate product.
     *
     * @param Illuminate\Http\Request
     * @return JSON
     */
    public function updatePremium()
    {
        $request = request()->all();
        $validator = Validator::make($request, ['item_id' => 'required|int', 'price' => 'required|numeric|min:0.01']);
        if ($validator->fails()) {
            return $validator->errors();
        }
        $user = auth()->user();
        $price = $this->priceRepo->updatePremium($user, $request['price'], $request['item_id']);
        if (isset($price['error'])) {
            return response()->json($price['error'], $price['status']);
        }
        return response()->json($price, HTTP_SUCCESS);
    }
}
