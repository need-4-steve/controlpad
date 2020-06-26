<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PayMan\EwalletService;
use App\Services\PayMan\PayManService;
use App\Http\Requests\PayTaxesCreditCardRequest;
use App\Http\Requests\PayTaxesEcheckRequest;

class EwalletTaxesController extends Controller
{
    public function __construct(
        EwalletService $ewalletService,
        PayManService $payManService
    ) {
        $this->ewalletService = $ewalletService;
        $this->payManService = $payManService;
    }

    public function payByCreditCard(PayTaxesCreditCardRequest $creditCardRequest)
    {
        $request = $creditCardRequest->all();
        $response = $this->ewalletService->payTaxesByCreditCard($request);
        return response()->json($response, HTTP_SUCCESS);
    }

    public function payByEcheck(PayTaxesEcheckRequest $echeckRequest)
    {
        $request = $echeckRequest->all();
        $response = $this->ewalletService->payTaxesByEcheck($request);
        return response()->json($response, HTTP_SUCCESS);
    }

    public function payByEwallet()
    {
        $request = request()->all();
        $response = $this->ewalletService->payTaxesByEwallet($request);
        return response()->json($response, HTTP_SUCCESS);
    }
}
