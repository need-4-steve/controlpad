<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShipmentCreateRequest;
use App\Http\Requests\ShipmentRateRequest;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\ShipmentRepository;
use App\Repositories\Eloquent\SettingRepository;
use App\Services\PayMan\PayManService;
use App\Services\Shippo\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ShippingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  AuthRepository $authRepo
     * @param  OrderRepository $orderRepo
     * @param  PayManService $paymentManager
     * @param  ShipmentRepository $shippingRepo
     * @param  SettingRepository $settingRepo
     * @param  ShippingService $shippingService
     * @return void
     */
    public function __construct(
        AuthRepository $authRepo,
        OrderRepository $orderRepo,
        PayManService $paymentManager,
        ShipmentRepository $shippingRepo,
        SettingRepository $settingRepo,
        ShippingService $shippingService
    ) {
        $this->authRepo = $authRepo;
        $this->orderRepo = $orderRepo;
        $this->paymentManager = $paymentManager;
        $this->settingRepo = $settingRepo;
        $this->shippingRepo = $shippingRepo;
        $this->shippingService = $shippingService;
    }

    /**
     * Get an index of available Carriers and their services for shipping.
     *
     * @return Response
     */
    public function getCarriers()
    {
        $carriers = $this->shippingRepo->getCarriers();
        return response()->json($carriers, HTTP_SUCCESS);
    }

    /**
     * Process shipping for a certain order so you can have
     * tracking available and be able to print labels.
     *
     * @param ShipmentCreateRequest $request {order_id: int, rate_id: string, payment: array}
     * @return Response
     */
    public function createShipping(ShipmentCreateRequest $request)
    {
        $request = $request->all();
        $authId = $this->authRepo->getOwnerId();

        $order = null;
        $orderId = null;
        if (isset($request['order_id'])) {
            $order = $this->orderRepo->find($request['order_id']);

            if ($order->store_owner_user_id !== $authId && !$this->authRepo->isOwnerAdmin()) {
                return response()->json('Unauthorized', 403);
            }

            if ($order->shipment()->first() !== null) {
                return response()->json('Order already has shipping', 400);
            }
            $orderId = $order->id;
        }

        $rate = $this->shippingService->getRate($request['rate_id']);
        $payment = $this->paymentManager->processShipping($rate, $request['payment'], $authId);
        if (!$payment['success']) {
            return response()->json($payment['error'], 400);
        }

        $shipping = $this->shippingService->createShipping($rate, $payment['transactionId'], $orderId);
        if (isset($shipping['error'])) {
            return response()->json($shipping['error'], $shipping['httpStatus']);
        }

        if ($order) {
            $order->status = 'fulfilled';
            $order->save();
        }

        return response()->json($shipping, 200);
    }

    /**
     * Get a list of rates pertaining to a shipment's to and from address
     * along with a parcel's size and weight.
     *
     * @param ShippingRateRequest $request
     * @return Response
     */
    public function rates(ShipmentRateRequest $request)
    {
        $request =  $request->all();
        $shipment = $this->shippingService->getRates(
            $request['address_from'],
            $request['address_to'],
            $request['parcel']
        );

        if (isset($shipment['error'])) {
            return response()->json($shipment['error'], $shipment['httpStatus']);
        }
        return response()->json($shipment['rates'], 200);
    }

    /**
     * Set the shippo settings for corporate.
     * TODO:create a view for these users to do this instead of doing this in the url
     * Example: /api/v1/shipping/settings?carriers=true&batching=true&shipping_team_id=company
     *
     * @return Response
     */
    public function settings()
    {
        // check if it is a controlpad user
        if (auth()->user()->id === 108 or auth()->user()->id === 109) {
            $request = request()->all();
            $carriers = null;
            $batchLabelCreate = null;
            $shippingTeamId = null;

            // Sets the Shippo api key and updates the carriers
            if (isset($request['carriers'])) {
                $carriers = $this->shippingService->setCarriers();
            }
            // Turns label batching on and off
            if (isset($request['batching'])) {
                $show = $request['batching'] === 'true' ? true: false;
                $inputs = ['batch_label_create' => ['show' => $show, 'value' => $show]];
                $batchLabelCreate = $this->settingRepo->update($inputs);
            }
            // Turns batching on and off
            if (isset($request['team_id'])) {
                $inputs = ['shipping_team_id' => ['show' => false, 'value' => $request['team_id']]];
                $shippingTeamId = $this->settingRepo->update($inputs);
            }
            return response()->json([
                'request' => $request,
                'carriers' => $carriers,
                'batch_label_create' => $batchLabelCreate,
                'shipping_team_id' => $shippingTeamId
            ], HTTP_SUCCESS);
        }
        return response()->json(null, HTTP_UNAUTHORIZED);
    }

    /**
     * Write orders to third party shipping service.
     *
     * @return Response
     */
    public function exportOrders()
    {
        $request = request()->all();
        $transferredOrders = $this->shippingService->exportOrders($request);
        return response()->json($transferredOrders, HTTP_SUCCESS);
    }
}
