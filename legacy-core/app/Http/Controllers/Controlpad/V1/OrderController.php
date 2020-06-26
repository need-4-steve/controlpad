<?php

namespace App\Http\Controllers\Controlpad\V1;

use Illuminate\Http\Request;
use App\Models\RegistrationToken;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function __construct(OrderRepository $orderRepo, UserRepository $userRepo)
    {
        $this->orderRepo = $orderRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * Get query of orders based on parameters submitted
     *
     * Post request:
     * @param customer_id (null for all, or specific id)
     * @param store_owner_user_id' (specific store owner, or all),
     * @param type (null for all, or type id:
     *           1- corp to rep
     *           2- corp to customer
     *           3- rep to customer
     *           4- rep to rep
     *           5- corp to admin),
     * @param fulfilled (null for all or fulfilled/unfulfilled),
     * @param start_date (date time string or null for last 30),
     * @param end_date (date time or null for now),
     * @param search_term (null to ignore, or match string for receipt id or purchaser name),
     * @param column (null for id or column to sort by),
     * @param order ('DESC' for descending, 'ASC' for ascending)
     *
     * @return Orders
     */
    public function index($request = null)
    {
        try {
            if (!$request) {
                $request = request()->all();
            }
            $orders = $this->orderRepo->buildOrderIndexQuery($request)->get();
            return $orders;
        } catch (Exception $e) {
            return "Error: " . $e;
        }
    }

    // order index endpoint matching Mcomm's expected format
    public function mcommIndex()
    {
        try {
            $request = request()->all();
            $validator = Validator::make($request, [
                'start_date' => 'required|date',
                'end_date' => 'required|date',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->messages(), 422);
            }
            $indexRequest = [
                'start_date' => $request['start_date'],
                'end_date' => $request['end_date']
            ];

            /* pull all sellers, api is currently working off of buyers
             * since all the orders are corp to rep
             */
            $indexRequest['store_owner_user_id'] = 'All';

            // if user is specified add user
            if (!empty($request['AssociateId'])) {
                $buyer = RegistrationToken::where('token', $request['AssociateId'])->first();
                if ($buyer != null) {
                    $indexRequest['customer_id'] = $buyer->user_id;
                } else {
                    $buyer = User::find($request['AssociateId']);
                    if (!empty($buyer)) {
                        $indexRequest['customer_id'] = $buyer->id;
                    } else {
                        // no user found, return error
                        return response()->json('User not found', 422);
                    }
                }
            }

            $orders = $this->index($indexRequest);
            $orderArray = [];

            foreach ($orders as $order) {
                $regToken = $this->userRepo->findRegistrationTokenId($order->customer_id);
                if (empty($regToken)) {
                    $regToken = 'Registration token not found';
                }
                $orderRow = [
                    'OrderNum'    => $order->receipt_id,
                    'AssociateId' => $regToken,
                    'BuyerId' => $order->customer_id,
                    'SellerId' => $order->store_owner_user_id,
                    'OrderDate'   => $order->created_at->toDateTimeString(),
                    // this is supposed to be subtotal, not overall total:
                    'TotalAmount' => $order->subtotal_price,
                    'ShipAmount'  => $order->total_shipping,
                    'TaxAmount'   => $order->total_tax,
                    'Discount'    => $order->total_discount,
                    // this is the overall total
                    'PaidAmount'  => $order->total_price
                ];
                $status = strtolower($order->status);
                switch ($status) {
                    case 'unfulfilled':
                        // paid but not shipped
                        $orderRow['Status'] = 1;
                        break;
                    case 'fulfilled':
                        // shipped
                        $orderRow['Status'] = 3;
                        break;
                    case 'cancelled':
                        // cancelled
                        $orderRow['Status'] = 2;
                        break;
                    // default to avoid overpaying commissions if new cancelled/refunded types are added
                    default:
                        $orderRow['Status'] = 2;
                }
                if ($order->store_owner_user_id == config('site.apex_user_id')) {
                    // wholesale
                    $orderRow['OrderType'] = 2;
                } else {
                    // retail
                    $orderRow['OrderType'] = 3;
                }

                $orderRow['OrderLines'] = [];
                $counter = 1;
                foreach ($order->lines as $orderline) {
                    $personalValue = 0;
                    $commissionValue = 0.00;

                    if (! empty($orderline->item)) {
                        $personalValue = 1;
                        $commissionValue = $orderline->price;
                    }

                    $orderRow['OrderLines'][] = [
                        'OrderNum'   => $orderline->order_id,
                        'ItemCode'   => $orderline->custom_sku,
                        'OrderLine'  => $counter++,
                        'UnitPrice'  => $orderline->price,
                        'Price'      => $orderline->price * $orderline->quantity,
                        'PV'         => $personalValue,
                        'CV'         => $orderline->price * $orderline->quantity,
                        'ParentLine' => $orderline->bundle_id,
                        'Quantity'   => $orderline->quantity
                    ];
                }

                $orderArray[] = $orderRow;
            }
            return response()->json($orderArray);
        } catch (Exception $e) {
            return "Error: " . $e;
        }
    }
}
